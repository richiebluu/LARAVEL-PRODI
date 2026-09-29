<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Keaktifan organisasi mahasiswa (kriteria C4 "Keaktifan Organisasi").
 * Poin dihitung dari JABATAN sesuai sheet "Skema Skor" pada Excel acuan.
 */
class Organisasi extends Model
{
    use HasFactory;

    protected $table = 'organisasi';

    /** ERD: primary key ORGANISASI = id_organisasi. */
    protected $primaryKey = 'id_organisasi';

    protected $fillable = [
        'nim',
        'nama_organisasi',
        'jabatan',
    ];

    /** ERD: ORGANISASI (N) -- MEMILIKI --> MAHASISWA (1), FK organisasi.nim. */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    /** Daftar jabatan resmi beserta skornya. */
    public static function daftarJabatan(): array
    {
        return config('saw.organisasi.jabatan', []);
    }

    /** Poin jabatan ini (0 bila jabatan tidak dikenal). */
    public function getPoinAttribute(): int
    {
        return (int) (self::daftarJabatan()[$this->jabatan] ?? 0);
    }
}
