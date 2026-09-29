<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    /** ERD: MAHASISWA (1) -- MENERIMA --> PENGUMUMAN (N), FK pengumuman.nim. */
    public function pengumuman(): HasMany
    {
        return $this->hasMany(Pengumuman::class, 'nim', 'nim');
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
