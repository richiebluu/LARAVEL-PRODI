<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DATA MASTER DOSEN (REVISI 26-09-2026).
 * Dosen bukan lagi role/akun pengguna: tidak punya login maupun dashboard.
 * Data dikelola Staff Prodi dan ditampilkan pada Profil Program Studi
 * (Dosen Pengajar & Struktur Organisasi).
 */
class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosen';

    /** ERD: primary key DOSEN = nuptk (bukan NIDN, bukan auto increment). */
    protected $primaryKey = 'nuptk';

    protected $keyType = 'string';

    public $incrementing = false;

    public const STATUS_AKTIF = 'aktif';
    // REVISI 28-09-2026: dosen yang sedang menempuh studi lanjut (mis. S3).
    public const STATUS_PENDIDIKAN = 'pendidikan';
    public const STATUS_NONAKTIF = 'nonaktif';

    /** Label status di UI (Dosen Pengajar). */
    public const LABEL_STATUS = [
        self::STATUS_AKTIF => 'Aktif',
        self::STATUS_PENDIDIKAN => 'Pendidikan',
        self::STATUS_NONAKTIF => 'Nonaktif',
    ];

    protected $fillable = [
        'nuptk',
        'nama',
        'foto',
        // REVISI 28-09-2026: kolom `jabatan` dihapus (jabatan ada di Struktur Organisasi).
        'pendidikan_terakhir', // REVISI 26-09-2026: dulu riwayat_pendidikan
        'google_scholar',     // link profil Google Scholar (pengganti halaman Publikasi)
        'email',
        'alamat',
        'tanggal_lahir',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    /** ERD: DOSEN (1) -- MENJABAT --> STRUKTUR_ORGANISASI (N), FK struktur_organisasi.dosen_id -> dosen.nuptk. */
    public function strukturOrganisasi(): HasMany
    {
        return $this->hasMany(StrukturOrganisasi::class, 'dosen_id', 'nuptk');
    }

    /** URL foto siap pakai (hasil upload atau URL luar). */
    public function getFotoUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->foto);
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    /** Urutan tampil: Aktif, Pendidikan, lalu Nonaktif. */
    public function scopeUrutStatus(Builder $query): Builder
    {
        return $query->orderByRaw("CASE status WHEN 'aktif' THEN 1 WHEN 'pendidikan' THEN 2 ELSE 3 END");
    }

    public function getLabelStatusAttribute(): string
    {
        return self::LABEL_STATUS[$this->status] ?? ucfirst((string) $this->status);
    }


    /** Kelas badge yang sudah ada di CSS website. */
    public function getBadgeStatusAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_AKTIF => 'badge-green',
            self::STATUS_PENDIDIKAN => 'badge-blue',
            default => 'badge-grey',
        };
    }

    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $query;
        }

        $kata = '%'.$kata.'%';

        return $query->where(function (Builder $q) use ($kata) {
            $q->where('nama', 'like', $kata)
                ->orWhere('nuptk', 'like', $kata)
                ->orWhere('pendidikan_terakhir', 'like', $kata);
        });
    }
}
