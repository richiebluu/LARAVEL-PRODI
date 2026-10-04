<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaranaPrasarana extends Model
{
    use HasFactory;

    protected $table = 'sarana_prasarana';

    protected $primaryKey = 'id_sarana_prasarana';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    public const GEDUNG = [
        'Gedung Teknik Informatika',
        'Adriansyah 1',
        'Adriansyah 2',
    ];

    public const IKON = 'fa-building';

    protected $fillable = [
        'staff_prodi_id',
        'nama',
        'gedung',
        'kapasitas',
        'fasilitas',
        'foto',
        'status',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
    ];

    public function staffProdi(): BelongsTo
    {
        return $this->belongsTo(StaffProdi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function scopeUrut(Builder $query): Builder
    {
        $kasus = collect(self::GEDUNG)->map(fn ($g, $i) => 'WHEN ? THEN '.($i + 1))->implode(' ');

        return $query->orderByRaw('CASE gedung '.$kasus.' ELSE 99 END', self::GEDUNG)
            ->orderBy('nama');
    }

    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $query;
        }

        $kata = '%'.$kata.'%';

        return $query->where(fn (Builder $q) => $q->where('nama', 'like', $kata)
            ->orWhere('gedung', 'like', $kata)
            ->orWhere('fasilitas', 'like', $kata));
    }

    public function getFotoUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->foto);
    }

    public function getIkonAttribute(): string
    {
        return self::IKON;
    }

    public function getDaftarFasilitasAttribute(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->fasilitas))
            ->map(fn ($b) => trim($b))
            ->filter()
            ->values()
            ->all();
    }
}
