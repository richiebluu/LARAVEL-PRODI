<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LowonganPekerjaan extends Model
{
    use HasFactory;

    protected $table = 'lowongan_pekerjaan';

    protected $primaryKey = 'id_lowongan_pekerjaan';

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
    ];

    protected $casts = [
        'batas_lamaran' => 'date',
    ];

    public function staffProdi(): BelongsTo
    {
        return $this->belongsTo(StaffProdi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function scopeTampil(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q->whereNull('batas_lamaran')->orWhereDate('batas_lamaran', '>=', now()->toDateString()));
    }

    public function getSudahDitutupAttribute(): bool
    {
        return $this->batas_lamaran !== null && $this->batas_lamaran->lt(now()->startOfDay());
    }
}
