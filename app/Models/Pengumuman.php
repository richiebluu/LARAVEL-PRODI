<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    /** ERD: primary key PENGUMUMAN = id_pengumuman. */
    protected $primaryKey = 'id_pengumuman';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_TERKIRIM = 'terkirim';

    /**
     * Pesan sistem (mis. hasil verifikasi prestasi) yang hanya tampil sebagai
     * notifikasi di dashboard mahasiswa, bukan di daftar pengumuman.
     * Menggantikan tabel lama `notifikasi` — sesuai ERD, atribut `notifikasi`
     * dan `dibaca_pada` berada pada entitas PENGUMUMAN.
     */
    public const STATUS_NOTIFIKASI = 'notifikasi';

    /**
     * Kategori pengumuman = kategori PRESTASI (Prestasi Akademik / Prestasi Non-Akademik).
     *
     * REVISI 24-09-2026: pengumuman ditujukan kepada MAHASISWA BERPRESTASI
     * (mahasiswa yang prestasinya sudah disetujui), BUKAN berdasarkan
     * perankingan/kriteria SAW. Karena itu kategori tidak lagi memakai
     * empat kriteria ranking, melainkan kategori prestasi yang diraih.
     */
    public static function daftarKategori(): array
    {
        return Prestasi::KATEGORI;
    }

    protected $fillable = [
        'prestasi_id',
        'kategori',
        'staff_prodi_id',
        'nim',
        'judul',
        'isi',
        'status',
        'tanggal_dikirim',
        'notifikasi',
        'dibaca_pada',
    ];

    protected $casts = [
        'tanggal_dikirim' => 'datetime',
        'dibaca_pada' => 'datetime',
    ];

    /** ERD: PENGUMUMAN (1) -- MENDAPAT --> PRESTASI (1), FK pengumuman.prestasi_id. */
    public function prestasi(): BelongsTo
    {
        return $this->belongsTo(Prestasi::class, 'prestasi_id', 'id_prestasi');
    }

    /** ERD: PENGUMUMAN (N) -- MEMBUAT --> STAFF_PRODI (1), FK pengumuman.staff_prodi_id. */
    public function staffProdi(): BelongsTo
    {
        return $this->belongsTo(StaffProdi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    /** ERD: PENGUMUMAN (N) -- MENERIMA --> MAHASISWA (1), FK pengumuman.nim (penerima pribadi). */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    /** Pengumuman sungguhan (bukan pesan notifikasi sistem). */
    public function scopePengumuman(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_NOTIFIKASI);
    }

    /** Pengumuman yang sudah dikirim ke mahasiswa. */
    public function scopeTerkirim(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_TERKIRIM);
    }

    /** Baris yang tampil sebagai notifikasi di dashboard mahasiswa. */
    public function scopeNotifikasiUntuk(Builder $query, string $nim): Builder
    {
        return $query->where('nim', $nim)
            ->whereNotNull('notifikasi')
            ->whereIn('status', [self::STATUS_TERKIRIM, self::STATUS_NOTIFIKASI]);
    }

    public function getBelumDibacaAttribute(): bool
    {
        return $this->dibaca_pada === null;
    }

    /** Judul notifikasi di dashboard mahasiswa. */
    public function getJudulNotifikasiAttribute(): string
    {
        return $this->status === self::STATUS_NOTIFIKASI ? $this->judul : 'Pengumuman baru untuk Anda';
    }

    /**
     * Label kategori untuk tampilan. Kategori disimpan pada kolom `kategori`;
     * data lama yang belum memiliki kategori menampilkan "-".
     */
    public function getLabelKategoriAttribute(): string
    {
        return $this->kategori ?: '-';
    }
}
