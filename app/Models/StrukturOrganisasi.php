<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrukturOrganisasi extends Model
{
    use HasFactory;

    protected $table = 'struktur_organisasi';

    protected $primaryKey = 'id_struktur_organisasi';

    public const JABATAN = [
        'Koordinator Program Studi' => 1,
        'Sekretaris Program Studi' => 2,
        'Koordinator Gugus TEFA' => 3,
        'Koordinator Gugus Kendali Mutu' => 3,
        'Koordinator Gugus Penelitian dan Pengabdian' => 3,
        'Koordinator Laboratorium' => 4,
        'Staff Prodi' => 5,
    ];

    public const TINGKAT_LAINNYA = 99;

    public const SARAN_JABATAN = [
        'Koordinator Program Studi',
        'Sekretaris Program Studi',
        'Koordinator Gugus TEFA',
        'Koordinator Gugus Kendali Mutu',
        'Koordinator Gugus Penelitian dan Pengabdian',
        'Koordinator Laboratorium',
        'Staff Prodi',
    ];

    public static function daftarJabatan(): array
    {
        return array_keys(self::JABATAN);
    }

    public static function tingkatJabatan(?string $jabatan): int
    {
        return self::JABATAN[(string) $jabatan] ?? self::TINGKAT_LAINNYA;
    }

    protected $fillable = [
        'program_studi_id',
        'dosen_id',
        'jabatan',
        'nama',
        'foto',
    ];

    public function scopeUrut(Builder $query): Builder
    {
        $daftar = self::SARAN_JABATAN;
        $kasus = collect($daftar)
            ->map(fn ($j, $i) => 'WHEN ? THEN '.($i + 1))
            ->implode(' ');

        return $query->orderByRaw('CASE jabatan '.$kasus.' ELSE '.self::TINGKAT_LAINNYA.' END', $daftar)
            ->orderBy('id_struktur_organisasi');
    }

    public function getTingkatAttribute(): int
    {
        return self::tingkatJabatan($this->jabatan);
    }

    public function getJabatanDiLuarDaftarAttribute(): bool
    {
        return ! array_key_exists((string) $this->jabatan, self::JABATAN);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id', 'id_program_studi');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id', 'nuptk');
    }

    public function getNamaPejabatAttribute(): string
    {
        return $this->dosen?->nama ?: ($this->nama ?: '-');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->foto) ?? $this->dosen?->foto_url;
    }
}
