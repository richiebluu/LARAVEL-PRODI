<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Akreditasi extends Model
{
    use HasFactory;

    protected $table = 'akreditasi';

    protected $primaryKey = 'id_akreditasi';

    protected $fillable = [
        'program_studi_id',
        'peringkat',
        'nomor_sk',
        'tanggal_mulai',
        'tanggal_berakhir',
        'lembaga',
        'dokumen',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
    ];

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id', 'id_program_studi');
    }

    public function getDokumenUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->dokumen);
    }

    public const STATUS_TERAKREDITASI = 'Terakreditasi';
    public const STATUS_BERAKHIR = 'Masa Berlaku Berakhir';
    public const STATUS_BELUM_BERLAKU = 'Belum Berlaku';
    public const STATUS_TIDAK_TERAKREDITASI = 'Tidak Terakreditasi';

    public const PERINGKAT_TIDAK_TERAKREDITASI = [
        'tidak terakreditasi',
        'tidak memenuhi syarat',
        'tidak memenuhi syarat peringkat',
    ];

    public function getStatusAttribute(): string
    {
        $hariIni = now()->startOfDay();

        if (in_array(mb_strtolower(trim((string) $this->peringkat)), self::PERINGKAT_TIDAK_TERAKREDITASI, true)) {
            return self::STATUS_TIDAK_TERAKREDITASI;
        }

        if ($this->tanggal_mulai && $this->tanggal_mulai->gt($hariIni)) {
            return self::STATUS_BELUM_BERLAKU;
        }

        if ($this->tanggal_berakhir && $this->tanggal_berakhir->lt($hariIni)) {
            return self::STATUS_BERAKHIR;
        }

        return self::STATUS_TERAKREDITASI;
    }

    public function getBerlakuAttribute(): bool
    {
        return $this->status === self::STATUS_TERAKREDITASI;
    }

    public function scopeTerakreditasi(Builder $query): Builder
    {
        $hariIni = now()->toDateString();
        $tanda = implode(', ', array_fill(0, count(self::PERINGKAT_TIDAK_TERAKREDITASI), '?'));

        return $query
            ->whereRaw('LOWER(TRIM(peringkat)) NOT IN ('.$tanda.')', self::PERINGKAT_TIDAK_TERAKREDITASI)
            ->where(fn ($q) => $q->whereNull('tanggal_mulai')->orWhereDate('tanggal_mulai', '<=', $hariIni))
            ->where(fn ($q) => $q->whereNull('tanggal_berakhir')->orWhereDate('tanggal_berakhir', '>=', $hariIni));
    }

    public function scopeTerbaru(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN tanggal_mulai IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('tanggal_berakhir')
            ->orderByDesc('id_akreditasi');
    }

    public static function utama(): ?self
    {
        return static::query()->terakreditasi()->terbaru()->first()
            ?? static::query()->terbaru()->first();
    }

    public function getTahunAttribute(): ?string
    {
        return $this->tanggal_mulai?->format('Y');
    }
}
