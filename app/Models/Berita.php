<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * BERITA PROGRAM STUDI (REVISI 26-09-2026).
 * Halaman berdiri sendiri berisi kegiatan/informasi terbaru Program Studi.
 */
class Berita extends Model
{
    use HasFactory;

    protected $table = 'berita';

    /** ERD: primary key BERITA = id_berita. */
    protected $primaryKey = 'id_berita';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_TERBIT = 'terbit';

    public const LABEL_STATUS = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_TERBIT => 'Terbit',
    ];

    protected $fillable = [
        'staff_prodi_id',
        'judul',
        'slug',
        'kategori',
        'ringkasan',
        'isi',
        'gambar',
        'tanggal',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /** URL detail memakai slug: /berita/{slug}. */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** ERD: BERITA (N) -- DIKELOLA --> STAFF_PRODI (1). */
    public function staffProdi(): BelongsTo
    {
        return $this->belongsTo(StaffProdi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function scopeTerbit(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_TERBIT);
    }

    public function getGambarUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->gambar);
    }

    /** Ringkasan untuk card: kolom ringkasan, atau potongan isi. */
    public function getCuplikanAttribute(): string
    {
        return $this->ringkasan ?: Str::limit(trim(strip_tags((string) $this->isi)), 140);
    }

    /** Paragraf isi berita (dipisah baris kosong/baris baru). */
    public function getParagrafAttribute(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->isi))
            ->map(fn ($b) => trim($b))
            ->filter()
            ->values()
            ->all();
    }

    /** Slug unik dari judul. */
    public static function buatSlug(string $judul, ?int $abaikanId = null): string
    {
        $dasar = Str::slug($judul) ?: 'berita';
        $slug = $dasar;
        $i = 2;

        while (static::where('slug', $slug)->when($abaikanId, fn ($q) => $q->where('id_berita', '!=', $abaikanId))->exists()) {
            $slug = $dasar.'-'.$i++;
        }

        return $slug;
    }
}
