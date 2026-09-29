<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LOWONGAN PEKERJAAN (REVISI 26-09-2026).
 * Website hanya menampilkan informasi dan mengarahkan ke sumber eksternal
 * (kolom `link`); proses lamaran tidak dilakukan di sistem ini.
 */
class LowonganPekerjaan extends Model
{
    use HasFactory;

    protected $table = 'lowongan_pekerjaan';

    /** ERD: primary key LOWONGAN_PEKERJAAN = id_lowongan_pekerjaan. */
    protected $primaryKey = 'id_lowongan_pekerjaan';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    public const TIPE = [
        'Penuh Waktu',
        'Paruh Waktu',
        'Magang',
        'Kontrak',
        'Freelance',
    ];

    protected $fillable = [
        'staff_prodi_id',
        'posisi',
        'perusahaan',
        'lokasi',
        'tipe',
        'deskripsi',
        'link',
        'batas_lamaran',
        'status',
    ];

    protected $casts = [
        'batas_lamaran' => 'date',
    ];

    /** ERD: LOWONGAN_PEKERJAAN (N) -- DIKELOLA --> STAFF_PRODI (1). */
    public function staffProdi(): BelongsTo
    {
        return $this->belongsTo(StaffProdi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    /** Lowongan yang tampil di publik: status aktif dan belum melewati batas lamaran. */
    public function scopeTampil(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF)
            ->where(fn ($q) => $q->whereNull('batas_lamaran')->orWhereDate('batas_lamaran', '>=', now()->toDateString()));
    }

    public function getSudahDitutupAttribute(): bool
    {
        return $this->batas_lamaran !== null && $this->batas_lamaran->lt(now()->startOfDay());
    }
}
