<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffProdi extends Model
{
    use HasFactory;

    protected $table = 'staff_prodi';

    protected $primaryKey = 'id_staff_prodi';

    protected $fillable = [
        'id_user',
        'nip',
        'nama',
        'foto',
        'jabatan',
        'email',
        'no_hp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->foto);
    }

    public function pengumuman(): HasMany
    {
        return $this->hasMany(Pengumuman::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function programStudi(): HasMany
    {
        return $this->hasMany(ProgramStudi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function berita(): HasMany
    {
        return $this->hasMany(Berita::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function lowonganPekerjaan(): HasMany
    {
        return $this->hasMany(LowonganPekerjaan::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function testimoni(): HasMany
    {
        return $this->hasMany(Testimoni::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function prospekLulusan(): HasMany
    {
        return $this->hasMany(ProspekLulusan::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function saranaPrasarana(): HasMany
    {
        return $this->hasMany(SaranaPrasarana::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function prestasiDiverifikasi(): HasMany
    {
        return $this->hasMany(Prestasi::class, 'staff_prodi_id', 'id_staff_prodi');
    }
}
