<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah';

    protected $primaryKey = 'kode_mata_kuliah';

    protected $keyType = 'string';

    public $incrementing = false;

    public const JENIS = [
        'Wajib',
        'Pilihan',
    ];

    public const SEMESTER_MAKS = 8;

    protected $fillable = [
        'program_studi_id',
        'kode_mata_kuliah',
        'nama',
        'semester',
        'sks',
        'jenis',
    ];

    protected $casts = [
        'program_studi_id' => 'integer',
        'semester' => 'integer',
        'sks' => 'integer',
    ];

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id', 'id_program_studi');
    }

    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderBy('semester')->orderBy('kode_mata_kuliah');
    }

    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $query;
        }

        return $query->where(fn ($q) => $q->where('kode_mata_kuliah', 'like', '%'.$kata.'%')->orWhere('nama', 'like', '%'.$kata.'%'));
    }
}
