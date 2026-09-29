<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SARANA & PRASARANA PROGRAM STUDI (REVISI 28-09-2026 tahap 2).
 * Termasuk nama-nama Laboratorium Prodi TI. Dikelola Staff Prodi (Data Master),
 * tampil di Profil > Sarana & Prasarana.
 */
class SaranaPrasarana extends Model
{
    use HasFactory;

    protected $table = 'sarana_prasarana';

    /** ERD: primary key SARANA_PRASARANA = id_sarana_prasarana. */
    protected $primaryKey = 'id_sarana_prasarana';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    public const JENIS_LAB = 'Laboratorium';

    /** Jenis sarana & prasarana => ikon Font Awesome yang sudah dipakai website. */
    public const JENIS = [
        self::JENIS_LAB => 'fa-flask',
        'Ruang Kuliah' => 'fa-chalkboard',
        'Ruang Penunjang' => 'fa-door-open',
        'Fasilitas Pendukung' => 'fa-wifi',
    ];

    protected $fillable = [
        'staff_prodi_id',
        'nama',
        'jenis',
        'lokasi',
        'kapasitas',
        'fasilitas',
        'deskripsi',
        'foto',
        'status',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
    ];

    /** SARANA_PRASARANA (N) -- DIKELOLA --> STAFF_PRODI (1). */
    public function staffProdi(): BelongsTo
    {
        return $this->belongsTo(StaffProdi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    /** Laboratorium lebih dulu, lalu nama (ERD tidak memiliki kolom urutan). */
    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderByRaw('CASE WHEN jenis = ? THEN 0 ELSE 1 END', [self::JENIS_LAB])
            ->orderBy('nama');
    }

    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $query;
        }

        $kata = '%'.$kata.'%';

        return $query->where(fn (Builder $q) => $q->where('nama', 'like', $kata)
            ->orWhere('lokasi', 'like', $kata)
            ->orWhere('fasilitas', 'like', $kata));
    }

    public function getFotoUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->foto);
    }

    public function getIkonAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? 'fa-building';
    }

    /** Fasilitas disimpan satu baris satu item. */
    public function getDaftarFasilitasAttribute(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->fasilitas))
            ->map(fn ($b) => trim($b))
            ->filter()
            ->values()
            ->all();
    }
}
