<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramStudi extends Model
{
    use HasFactory;

    protected $table = 'program_studi';

    /** ERD: primary key PROGRAM_STUDI = id_program_studi. */
    protected $primaryKey = 'id_program_studi';

    protected $fillable = [
        'staff_prodi_id',
        'nama_prodi',
        'deskripsi',
        'visi',
        'misi',
        'jumlah_alumni',
        'jumlah_dosen',
        // Menu Informasi: AKAMAWA + PDF Kode Etik Mahasiswa sebagai atribut Profil Prodi.
        // REVISI 27-09-2026: link tutorial AKAMAWA dihapus ("AKAMAWA / Tutorial" -> "AKAMAWA").
        'link_akamawa',
        'kode_etik',
    ];

    /** Alamat bawaan layanan AKAMAWA Politala bila Staff Prodi belum mengisi link. */
    public const LINK_AKAMAWA_BAWAAN = 'https://akamawa.politala.ac.id/';

    protected $casts = [
        'jumlah_alumni' => 'integer',
        'jumlah_dosen' => 'integer',
    ];

    /** ERD: PROGRAM_STUDI (1) -- MEMILIKI --> STRUKTUR_ORGANISASI (N). */
    public function strukturOrganisasi(): HasMany
    {
        return $this->hasMany(StrukturOrganisasi::class, 'program_studi_id', 'id_program_studi')->urut();
    }

    /** Link AKAMAWA yang dipakai tombol "Kunjungi Website AKAMAWA". */
    public function getUrlAkamawaAttribute(): string
    {
        return $this->link_akamawa ?: self::LINK_AKAMAWA_BAWAAN;
    }

    /** URL PDF Kode Etik (hasil upload Staff Prodi). */
    public function getKodeEtikUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->kode_etik);
    }

    /** ERD: PROGRAM_STUDI (1) -- MEMILIKI --> AKREDITASI (N). */
    public function akreditasi(): HasMany
    {
        return $this->hasMany(Akreditasi::class, 'program_studi_id', 'id_program_studi');
    }

    /** Riwayat akreditasi, urut tanggal penetapan terbaru (lihat Akreditasi::scopeTerbaru). */
    public function akreditasiTerbaru(): HasMany
    {
        return $this->hasMany(Akreditasi::class, 'program_studi_id', 'id_program_studi')->terbaru();
    }

    /** Akreditasi yang sedang berlaku (status Terakreditasi), terbaru lebih dulu. */
    public function akreditasiBerlaku(): HasMany
    {
        return $this->hasMany(Akreditasi::class, 'program_studi_id', 'id_program_studi')->terakreditasi()->terbaru();
    }

    /** ERD: STAFF_PRODI (1) -- MENGELOLA --> PROGRAM_STUDI (1). */
    public function staffProdi(): BelongsTo
    {
        return $this->belongsTo(StaffProdi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    /** Misi disimpan satu baris satu poin. */
    public function getMisiListAttribute(): array
    {
        return $this->pecahBaris($this->misi);
    }

    private function pecahBaris(?string $teks): array
    {
        if (blank($teks)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $teks))
            ->map(fn ($baris) => trim($baris))
            ->filter()
            ->values()
            ->all();
    }
}
