<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Organisasi extends Model
{
    use HasFactory;

    protected $table = 'organisasi';

    protected $primaryKey = 'id_organisasi';

    protected $fillable = [
        'nim',
        'nama_organisasi',
        'jabatan',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public static function daftarJabatan(): array
    {
        return config('saw.organisasi.jabatan', []);
    }

    public function getPoinAttribute(): int
    {
        return (int) (self::daftarJabatan()[$this->jabatan] ?? 0);
    }
}
