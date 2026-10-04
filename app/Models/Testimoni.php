<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimoni extends Model
{
    use HasFactory;

    protected $table = 'testimoni';

    protected $primaryKey = 'id_testimoni';

    protected $fillable = [
        'staff_prodi_id',
        'nama',
        'tahun_kelulusan',
        'nama_perusahaan',
        'jabatan',
        'foto',
        'isi',
    ];

    protected $casts = [
        'tahun_kelulusan' => 'integer',
    ];

    public function staffProdi(): BelongsTo
    {
        return $this->belongsTo(StaffProdi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function getKeteranganAlumniAttribute(): string
    {
        $bagian = array_filter([
            $this->tahun_kelulusan ? 'Alumni '.$this->tahun_kelulusan : 'Alumni',
            $this->jabatan,
            $this->nama_perusahaan,
        ]);

        return implode(' · ', $bagian);
    }

    public function getFotoUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->foto);
    }
}
