<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Prestasi extends Model
{
    use HasFactory;

    protected $table = 'prestasi';

    protected $primaryKey = 'id_prestasi';

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    public const STATUS = [
        self::STATUS_MENUNGGU,
        self::STATUS_DISETUJUI,
        self::STATUS_DITOLAK,
    ];

    public const LABEL_STATUS = [
        self::STATUS_MENUNGGU => 'Menunggu Verifikasi',
        self::STATUS_DISETUJUI => 'Disetujui',
        self::STATUS_DITOLAK => 'Ditolak',
    ];

    public const KATEGORI_AKADEMIK = 'Prestasi Akademik';
    public const KATEGORI_NON_AKADEMIK = 'Prestasi Non-Akademik';

    public const KATEGORI = [
        self::KATEGORI_AKADEMIK,
        self::KATEGORI_NON_AKADEMIK,
    ];

    public static function daftarTingkat(): array
    {
        return config('saw.tingkat', []);
    }

    protected $fillable = [
        'nim',
        'staff_prodi_id',
        'judul',
        'kategori',
        'tingkat',
        'penyelenggara',
        'tanggal',
        'dokumen',
        'deskripsi',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function staffProdi(): BelongsTo
    {
        return $this->belongsTo(StaffProdi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function pengumuman(): HasOne
    {
        return $this->hasOne(Pengumuman::class, 'prestasi_id', 'id_prestasi')
            ->where('status', '!=', Pengumuman::STATUS_NOTIFIKASI);
    }

    public function getDokumenUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->dokumen);
    }

    public static function skema(?string $kategori): array
    {
        return $kategori === self::KATEGORI_NON_AKADEMIK
            ? config('saw.prestasi_non_akademik')
            : config('saw.prestasi_akademik');
    }

    public function getLabelStatusAttribute(): string
    {
        return self::LABEL_STATUS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getPoinAttribute(): int
    {
        return (int) (self::skema($this->kategori)['skor'][$this->tingkat] ?? 0);
    }

    public function scopeAkademik(Builder $query): Builder
    {
        return $query->where('kategori', self::KATEGORI_AKADEMIK);
    }

    public function scopeNonAkademik(Builder $query): Builder
    {
        return $query->where('kategori', self::KATEGORI_NON_AKADEMIK);
    }

    public function scopeDisetujui(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DISETUJUI);
    }

    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MENUNGGU);
    }
}
