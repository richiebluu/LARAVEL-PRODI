<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    /** ERD: primary key MAHASISWA = nim (string, bukan auto increment). */
    protected $primaryKey = 'nim';

    protected $keyType = 'string';

    public $incrementing = false;

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_ALUMNI = 'alumni';
    public const STATUS_CUTI = 'cuti';
    public const STATUS_NONAKTIF = 'nonaktif';
    // REVISI 24-09-2026: status DO (Drop Out) dan DISPEN (Dispensasi).
    public const STATUS_DO = 'do';
    public const STATUS_DISPEN = 'dispen';

    public const STATUS = [
        self::STATUS_AKTIF,
        self::STATUS_ALUMNI,
        self::STATUS_CUTI,
        self::STATUS_NONAKTIF,
        self::STATUS_DO,
        self::STATUS_DISPEN,
    ];

    /** Label status untuk dropdown, tabel, dan detail. */
    public const LABEL_STATUS = [
        self::STATUS_AKTIF => 'Aktif',
        self::STATUS_ALUMNI => 'Alumni',
        self::STATUS_CUTI => 'Cuti',
        self::STATUS_NONAKTIF => 'Nonaktif',
        self::STATUS_DO => 'DO',
        self::STATUS_DISPEN => 'DISPEN',
    ];

    protected $fillable = [
        'nim',
        'user_id',
        'nama',
        'foto',
        'angkatan',
        'kelas',
        'email',
        'no_hp',
        'ipk',
        'status_mahasiswa',
    ];

    protected $casts = [
        'angkatan' => 'integer',
        'ipk' => 'decimal:2',
    ];

    /** ERD: MAHASISWA (1) -- MEMILIKI --> USERS (1). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    /** ERD: MAHASISWA (1) -- MENGAJUKAN --> PRESTASI (N), FK prestasi.nim. */
    public function prestasi(): HasMany
    {
        return $this->hasMany(Prestasi::class, 'nim', 'nim');
    }

    /** Prestasi yang sudah disetujui saja. */
    public function prestasiDisetujui(): HasMany
    {
        return $this->hasMany(Prestasi::class, 'nim', 'nim')->where('status', Prestasi::STATUS_DISETUJUI);
    }

    /** ERD: MAHASISWA (1) -- MEMILIKI --> ORGANISASI (N), FK organisasi.nim. Sumber kriteria Keaktifan Organisasi. */
    public function organisasi(): HasMany
    {
        return $this->hasMany(Organisasi::class, 'nim', 'nim');
    }

    /** ERD: MAHASISWA (1) -- MEMILIKI --> RANKING (N), FK ranking.nim. */
    public function ranking(): HasMany
    {
        return $this->hasMany(Ranking::class, 'nim', 'nim');
    }

    /**
     * Pesan sistem pribadi (pengumuman berstatus "notifikasi", mis. hasil verifikasi
     * prestasi) — FK pengumuman.nim.
     */
    public function pengumuman(): HasMany
    {
        return $this->hasMany(Pengumuman::class, 'nim', 'nim');
    }

    /**
     * REVISI DOSEN 01-10-2026: pengumuman Staff Prodi yang diterima mahasiswa ini.
     * MAHASISWA (N) -- MENERIMA -- (N) PENGUMUMAN lewat tabel pivot pengumuman_penerima,
     * lengkap dengan status baca (pivot.dibaca_pada) per mahasiswa.
     */
    public function pengumumanDiterima(): BelongsToMany
    {
        return $this->belongsToMany(Pengumuman::class, 'pengumuman_penerima', 'nim', 'pengumuman_id', 'nim', 'id_pengumuman')
            ->withPivot('dibaca_pada')
            ->withTimestamps();
    }

    /** Label status yang ditampilkan (contoh: "do" -> "DO"). */
    public function getLabelStatusAttribute(): string
    {
        return self::LABEL_STATUS[$this->status_mahasiswa] ?? ucfirst((string) $this->status_mahasiswa);
    }

    /**
     * Mahasiswa berprestasi = mahasiswa yang memiliki minimal satu prestasi
     * berstatus "disetujui". Dipakai halaman Mahasiswa Berprestasi dan
     * sebagai dasar penerima pengumuman (bukan berdasarkan ranking).
     */
    public function scopeBerprestasi(Builder $query): Builder
    {
        return $query->whereHas('prestasiDisetujui');
    }

    /**
     * REVISI DOSEN 01-10-2026: mahasiswa yang email-nya memakai domain institusi
     * mahasiswa (@mhs.politala.ac.id, config auth.domain_email.mahasiswa).
     * Dicek pada email profil (mahasiswa.email) maupun email akun login (users.email).
     */
    public function scopeEmailInstitusi(Builder $query): Builder
    {
        $akhiran = '%@'.strtolower((string) User::domainEmail('mahasiswa'));

        return $query->where(fn ($q) => $q
            ->whereRaw('LOWER(mahasiswa.email) LIKE ?', [$akhiran])
            ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(email) LIKE ?', [$akhiran])));
    }

    /** Email yang dipakai untuk pengumuman (email akun login = email profil). */
    public function getEmailKontakAttribute(): ?string
    {
        return $this->user?->email ?? $this->email;
    }

    /** URL foto siap pakai (hasil upload atau URL luar). */
    public function getFotoUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->foto);
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status_mahasiswa', self::STATUS_AKTIF);
    }

    public function scopeAlumni(Builder $query): Builder
    {
        return $query->where('status_mahasiswa', self::STATUS_ALUMNI);
    }

    /** Pencarian nama / NIM / kelas langsung ke database. */
    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $query;
        }

        $kata = '%'.$kata.'%';

        return $query->where(function (Builder $q) use ($kata) {
            $q->where('nama', 'like', $kata)
                ->orWhere('nim', 'like', $kata)
                ->orWhere('kelas', 'like', $kata);
        });
    }
}
