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

    protected $primaryKey = 'nim';

    protected $keyType = 'string';

    public $incrementing = false;

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_ALUMNI = 'alumni';
    public const STATUS_CUTI = 'cuti';
    public const STATUS_NONAKTIF = 'nonaktif';
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public function prestasi(): HasMany
    {
        return $this->hasMany(Prestasi::class, 'nim', 'nim');
    }

    public function prestasiDisetujui(): HasMany
    {
        return $this->hasMany(Prestasi::class, 'nim', 'nim')->where('status', Prestasi::STATUS_DISETUJUI);
    }

    public function organisasi(): HasMany
    {
        return $this->hasMany(Organisasi::class, 'nim', 'nim');
    }

    public function ranking(): HasMany
    {
        return $this->hasMany(Ranking::class, 'nim', 'nim');
    }

    public function pengumuman(): HasMany
    {
        return $this->hasMany(Pengumuman::class, 'nim', 'nim');
    }

    public function pengumumanDiterima(): BelongsToMany
    {
        return $this->belongsToMany(Pengumuman::class, 'pengumuman_penerima', 'nim', 'pengumuman_id', 'nim', 'id_pengumuman')
            ->withPivot('dibaca_pada')
            ->withTimestamps();
    }

    public function getLabelStatusAttribute(): string
    {
        return self::LABEL_STATUS[$this->status_mahasiswa] ?? ucfirst((string) $this->status_mahasiswa);
    }

    public function scopeBerprestasi(Builder $query): Builder
    {
        return $query->whereHas('prestasiDisetujui');
    }

    public function scopeEmailInstitusi(Builder $query): Builder
    {
        $akhiran = '%@'.strtolower((string) User::domainEmail('mahasiswa'));

        return $query->where(fn ($q) => $q
            ->whereRaw('LOWER(mahasiswa.email) LIKE ?', [$akhiran])
            ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(email) LIKE ?', [$akhiran])));
    }

    public function getEmailKontakAttribute(): ?string
    {
        return $this->user?->email ?? $this->email;
    }

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
