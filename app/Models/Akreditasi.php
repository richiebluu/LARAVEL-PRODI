<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Akreditasi extends Model
{
    use HasFactory;

    protected $table = 'akreditasi';

    /** ERD: primary key AKREDITASI = id_akreditasi. */
    protected $primaryKey = 'id_akreditasi';

    protected $fillable = [
        'program_studi_id',
        'peringkat',
        'nomor_sk',
        'tanggal_mulai',
        'tanggal_berakhir',
        'lembaga',
        'dokumen',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
    ];

    /** ERD: AKREDITASI (N) -- MEMILIKI --> PROGRAM_STUDI (1). */
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id', 'id_program_studi');
    }

    /** URL dokumen SK akreditasi. */
    public function getDokumenUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->dokumen);
    }

    /* ======================================================================
     *  REVISI DOSEN (01-10-2026) — AKREDITASI YANG BENAR-BENAR BERLAKU
     *  Status diturunkan dari peringkat + tanggal (tidak ada kolom status di ERD):
     *    - peringkat "Tidak Terakreditasi"/"Tidak Memenuhi Syarat ..."  -> Tidak Terakreditasi
     *    - tanggal_mulai di masa depan                                  -> Belum Berlaku
     *    - tanggal_berakhir sudah lewat                                 -> Masa Berlaku Berakhir
     *    - selain itu                                                   -> Terakreditasi
     *  Akreditasi UTAMA = data berstatus Terakreditasi dengan tanggal penetapan
     *  (tanggal_mulai) PALING BARU — bukan sekadar ID terakhir.
     * ==================================================================== */

    public const STATUS_TERAKREDITASI = 'Terakreditasi';
    public const STATUS_BERAKHIR = 'Masa Berlaku Berakhir';
    public const STATUS_BELUM_BERLAKU = 'Belum Berlaku';
    public const STATUS_TIDAK_TERAKREDITASI = 'Tidak Terakreditasi';

    /** Peringkat (huruf kecil) yang berarti program studi TIDAK terakreditasi. */
    public const PERINGKAT_TIDAK_TERAKREDITASI = [
        'tidak terakreditasi',
        'tidak memenuhi syarat',
        'tidak memenuhi syarat peringkat',
    ];

    /** Status akreditasi dihitung dari peringkat dan masa berlaku. */
    public function getStatusAttribute(): string
    {
        $hariIni = now()->startOfDay();

        if (in_array(mb_strtolower(trim((string) $this->peringkat)), self::PERINGKAT_TIDAK_TERAKREDITASI, true)) {
            return self::STATUS_TIDAK_TERAKREDITASI;
        }

        if ($this->tanggal_mulai && $this->tanggal_mulai->gt($hariIni)) {
            return self::STATUS_BELUM_BERLAKU;
        }

        if ($this->tanggal_berakhir && $this->tanggal_berakhir->lt($hariIni)) {
            return self::STATUS_BERAKHIR;
        }

        return self::STATUS_TERAKREDITASI;
    }

    /** true bila akreditasi ini berstatus "Terakreditasi" (masih berlaku). */
    public function getBerlakuAttribute(): bool
    {
        return $this->status === self::STATUS_TERAKREDITASI;
    }

    /**
     * Query: hanya akreditasi berstatus "Terakreditasi" (logika sama persis dengan
     * getStatusAttribute, tetapi dijalankan di database).
     */
    public function scopeTerakreditasi(Builder $query): Builder
    {
        $hariIni = now()->toDateString();
        $tanda = implode(', ', array_fill(0, count(self::PERINGKAT_TIDAK_TERAKREDITASI), '?'));

        return $query
            ->whereRaw('LOWER(TRIM(peringkat)) NOT IN ('.$tanda.')', self::PERINGKAT_TIDAK_TERAKREDITASI)
            ->where(fn ($q) => $q->whereNull('tanggal_mulai')->orWhereDate('tanggal_mulai', '<=', $hariIni))
            ->where(fn ($q) => $q->whereNull('tanggal_berakhir')->orWhereDate('tanggal_berakhir', '>=', $hariIni));
    }

    /**
     * Urutan "terbaru" berdasarkan TANGGAL: tanggal penetapan (tanggal_mulai) paling baru
     * lebih dulu (data tanpa tanggal ditaruh di akhir), lalu tanggal berakhir paling jauh.
     * ID hanya dipakai sebagai pemisah terakhir bila tanggalnya sama persis.
     */
    public function scopeTerbaru(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN tanggal_mulai IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('tanggal_berakhir')
            ->orderByDesc('id_akreditasi');
    }

    /**
     * Akreditasi UTAMA yang ditampilkan di halaman publik, beranda, profil, dan
     * pratinjau badge Staff Prodi:
     *   1. Cari akreditasi berstatus "Terakreditasi" dengan tanggal penetapan terbaru.
     *   2. Bila tidak ada yang berlaku, tampilkan data dengan tanggal terbaru apa adanya
     *      (statusnya tetap jujur, mis. "Masa Berlaku Berakhir").
     */
    public static function utama(): ?self
    {
        return static::query()->terakreditasi()->terbaru()->first()
            ?? static::query()->terbaru()->first();
    }

    /** Tahun penetapan diambil dari tanggal mulai. */
    public function getTahunAttribute(): ?string
    {
        return $this->tanggal_mulai?->format('Y');
    }
}
