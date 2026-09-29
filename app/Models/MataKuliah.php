<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * MATA KULIAH — data Kurikulum Program Studi (REVISI 28-09-2026).
 * Isinya disesuaikan dengan data mata kuliah di SIPADU (diinput/diimpor Staff Prodi).
 */
class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah';

    /** Jenis mata kuliah pada kurikulum. */
    public const JENIS = [
        'Wajib',
        'Pilihan',
    ];

    /** Batas nomor semester yang diterima form/impor (D3 umumnya 6 semester). */
    public const SEMESTER_MAKS = 8;

    protected $fillable = [
        'kode',
        'nama',
        'semester',
        'sks',
        'jenis',
    ];

    protected $casts = [
        'semester' => 'integer',
        'sks' => 'integer',
    ];

    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderBy('semester')->orderBy('kode');
    }

    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $query;
        }

        return $query->where(fn ($q) => $q->where('kode', 'like', '%'.$kata.'%')->orWhere('nama', 'like', '%'.$kata.'%'));
    }
}
