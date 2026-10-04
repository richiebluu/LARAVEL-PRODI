<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosen';

    protected $primaryKey = 'nuptk';

    protected $keyType = 'string';

    public $incrementing = false;

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_PENDIDIKAN = 'pendidikan';
    public const STATUS_NONAKTIF = 'nonaktif';

    public const LABEL_STATUS = [
        self::STATUS_AKTIF => 'Aktif',
        self::STATUS_PENDIDIKAN => 'Pendidikan',
        self::STATUS_NONAKTIF => 'Nonaktif',
    ];

    protected $fillable = [
        'nuptk',
        'nama',
        'foto',
        'pendidikan_terakhir',
        'google_scholar',
        'email',
        'alamat',
        'tanggal_lahir',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function strukturOrganisasi(): HasMany
    {
        return $this->hasMany(StrukturOrganisasi::class, 'dosen_id', 'nuptk');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->foto);
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function scopeUrutStatus(Builder $query): Builder
    {
        return $query->orderByRaw("CASE status WHEN 'aktif' THEN 1 WHEN 'pendidikan' THEN 2 ELSE 3 END");
    }

    public function getLabelStatusAttribute(): string
    {
        return self::LABEL_STATUS[$this->status] ?? ucfirst((string) $this->status);
    }

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
