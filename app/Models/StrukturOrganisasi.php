<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * STRUKTUR ORGANISASI PROGRAM STUDI (REVISI 26-09-2026).
 * Bagian dari Profil Program Studi: Koordinator Program Studi, Koordinator Gugus,
 * dan jabatan lain beserta nama pejabatnya. Pejabat dapat dihubungkan ke Data
 * Master Dosen (dosen_id) atau diisi nama saja (mis. Staff Prodi).
 */
class StrukturOrganisasi extends Model
{
    use HasFactory;

    protected $table = 'struktur_organisasi';

    /** ERD: primary key STRUKTUR_ORGANISASI = id_struktur_organisasi. */
    protected $primaryKey = 'id_struktur_organisasi';

    /** Saran jabatan pada form (Staff Prodi tetap boleh mengetik jabatan lain). */
    public const SARAN_JABATAN = [
        'Koordinator Program Studi',
        'Sekretaris Program Studi',
        'Koordinator Gugus TEFA',
        'Koordinator Gugus Kendali Mutu',
        'Koordinator Gugus Penelitian dan Pengabdian',
        'Koordinator Laboratorium',
        'Staff Prodi',
    ];

    protected $fillable = [
        'program_studi_id',
        'dosen_id',
        'jabatan',
        'nama',
        'foto', // REVISI 27-09-2026: upload foto pejabat
    ];

    /**
     * Urutan tampil mengikuti hierarki jabatan (SARAN_JABATAN), lalu urutan input.
     * ERD tidak memiliki kolom `urutan`, jadi urutan tidak disimpan di database.
     */
    public function scopeUrut(Builder $query): Builder
    {
        $kasus = collect(self::SARAN_JABATAN)
            ->map(fn ($j, $i) => 'WHEN ? THEN '.($i + 1))
            ->implode(' ');

        return $query->orderByRaw('CASE jabatan '.$kasus.' ELSE 99 END', self::SARAN_JABATAN)
            ->orderBy('id_struktur_organisasi');
    }

    /** ERD: STRUKTUR_ORGANISASI (N) -- BAGIAN DARI --> PROGRAM_STUDI (1). */
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id', 'id_program_studi');
    }

    /** ERD: STRUKTUR_ORGANISASI (N) -- DIJABAT --> DOSEN (1), opsional. */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id', 'nuptk');
    }

    /** Nama pejabat: dari data dosen bila terhubung, selain itu kolom nama. */
    public function getNamaPejabatAttribute(): string
    {
        return $this->dosen?->nama ?: ($this->nama ?: '-');
    }

    /** Foto yang diunggah pada Struktur Organisasi; bila kosong memakai foto data dosen. */
    public function getFotoUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->foto) ?? $this->dosen?->foto_url;
    }
}
