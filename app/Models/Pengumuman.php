<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    protected $primaryKey = 'id_pengumuman';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_TERKIRIM = 'terkirim';

    public const STATUS_NOTIFIKASI = 'notifikasi';

    public static function daftarKategori(): array
    {
        return Prestasi::KATEGORI;
    }

    protected $fillable = [
        'prestasi_id',
        'kategori',
        'staff_prodi_id',
        'nim',
        'judul',
        'isi',
        'status',
        'tanggal_dikirim',
        'notifikasi',
        'dibaca_pada',
    ];

    protected $casts = [
        'tanggal_dikirim' => 'datetime',
        'dibaca_pada' => 'datetime',
    ];

    public function prestasi(): BelongsTo
    {
        return $this->belongsTo(Prestasi::class, 'prestasi_id', 'id_prestasi');
    }

    public function staffProdi(): BelongsTo
    {
        return $this->belongsTo(StaffProdi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function penerima(): BelongsToMany
    {
        return $this->belongsToMany(Mahasiswa::class, 'pengumuman_penerima', 'pengumuman_id', 'nim', 'id_pengumuman', 'nim')
            ->withPivot('dibaca_pada')
            ->withTimestamps();
    }

    public function scopePengumuman(Builder $query): Builder
    {
        return $query->where('pengumuman.status', '!=', self::STATUS_NOTIFIKASI);
    }

    public function scopeTerkirim(Builder $query): Builder
    {
        return $query->where('pengumuman.status', self::STATUS_TERKIRIM);
    }

    public function scopeNotifikasiUntuk(Builder $query, string $nim): Builder
    {
        return $query->where('pengumuman.nim', $nim)
            ->whereNotNull('pengumuman.notifikasi')
            ->where('pengumuman.status', self::STATUS_NOTIFIKASI);
    }

    public function getBelumDibacaAttribute(): bool
    {
        if ($this->relationLoaded('pivot') && $this->pivot) {
            return $this->pivot->dibaca_pada === null;
        }

        return $this->dibaca_pada === null;
    }

    public function getRingkasanPenerimaAttribute(): string
    {
        $nama = $this->penerima->pluck('nama');

        if ($nama->isEmpty()) {
            return '-';
        }

        return $nama->take(2)->implode(', ').($nama->count() > 2 ? ' +'.($nama->count() - 2).' lainnya' : '');
    }

    public function getJudulNotifikasiAttribute(): string
    {
        return $this->status === self::STATUS_NOTIFIKASI ? $this->judul : 'Pengumuman baru untuk Anda';
    }

    public function getLabelKategoriAttribute(): string
    {
        return $this->kategori ?: '-';
    }
}
