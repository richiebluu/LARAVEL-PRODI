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

    /** ERD: primary key STAFF_PRODI = id_staff_prodi; NIP = identitas unik staff. */
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

    /** ERD: STAFF_PRODI (1) -- MEMILIKI --> USERS (1). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /** URL foto siap pakai (hasil upload atau URL luar). */
    public function getFotoUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->foto);
    }

    /** ERD: STAFF_PRODI (1) -- MEMBUAT --> PENGUMUMAN (N). */
    public function pengumuman(): HasMany
    {
        return $this->hasMany(Pengumuman::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    /** ERD: STAFF_PRODI (1) -- MENGELOLA --> PROGRAM_STUDI. */
    public function programStudi(): HasMany
    {
        return $this->hasMany(ProgramStudi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    /** ERD: STAFF_PRODI (1) -- MENGELOLA --> BERITA (N). */
    public function berita(): HasMany
    {
        return $this->hasMany(Berita::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    /** ERD: STAFF_PRODI (1) -- MENGELOLA --> LOWONGAN_PEKERJAAN (N). */
    public function lowonganPekerjaan(): HasMany
    {
        return $this->hasMany(LowonganPekerjaan::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    /** ERD: STAFF_PRODI (1) -- MENGELOLA --> TESTIMONI (N). */
    public function testimoni(): HasMany
    {
        return $this->hasMany(Testimoni::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    /** ERD: STAFF_PRODI (1) -- MENGELOLA --> PROSPEK_LULUSAN (N). REVISI 28-09-2026. */
    public function prospekLulusan(): HasMany
    {
        return $this->hasMany(ProspekLulusan::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    /** ERD: STAFF_PRODI (1) -- MENGELOLA --> KEGIATAN_MAHASISWA (N). */
    public function kegiatanMahasiswa(): HasMany
    {
        return $this->hasMany(KegiatanMahasiswa::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    /** ERD: STAFF_PRODI (1) -- MENGELOLA --> SARANA_PRASARANA (N). */
    public function saranaPrasarana(): HasMany
    {
        return $this->hasMany(SaranaPrasarana::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    /** ERD: STAFF_PRODI (1) -- MEMVERIFIKASI --> PRESTASI (N). */
    public function prestasiDiverifikasi(): HasMany
    {
        return $this->hasMany(Prestasi::class, 'staff_prodi_id', 'id_staff_prodi');
    }
}
