<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Berita extends Model
{
    use HasFactory;

    protected $table = 'berita';

    protected $primaryKey = 'id_berita';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_TERBIT = 'terbit';

    public const LABEL_STATUS = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_TERBIT => 'Terbit',
    ];

    public const JENIS_BERITA = 'berita';
    public const JENIS_KEGIATAN = 'kegiatan_mahasiswa';

    public const LABEL_JENIS = [
        self::JENIS_BERITA => 'Kegiatan Prodi',
        self::JENIS_KEGIATAN => 'Kegiatan Mahasiswa',
    ];

    public const SLUG_JENIS = [
        'kegiatan-prodi' => self::JENIS_BERITA,
        'kegiatan-mahasiswa' => self::JENIS_KEGIATAN,
    ];

    public const KATEGORI_KEGIATAN = [
        'Seminar & Workshop',
        'Lomba & Kompetisi',
        'Pengabdian Masyarakat',
        'Kunjungan Industri',
        'Organisasi & Kepanitiaan',
        'Pelatihan & Sertifikasi',
        'Lainnya',
    ];

    protected $fillable = [
        'staff_prodi_id',
        'judul',
        'slug',
        'jenis',
        'kategori',
        'lokasi',
        'penyelenggara',
        'ringkasan',
        'isi',
        'gambar',
        'link_media_sosial',
        'tanggal',
        'status',
    ];

    protected $attributes = [
        'jenis' => self::JENIS_BERITA,
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function staffProdi(): BelongsTo
    {
        return $this->belongsTo(StaffProdi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function scopeTerbit(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_TERBIT);
    }

    public function scopeJenis(Builder $query, ?string $jenis): Builder
    {
        return $jenis ? $query->where('jenis', $jenis) : $query;
    }

    public function getLabelJenisAttribute(): string
    {
        return self::LABEL_JENIS[$this->jenis] ?? self::LABEL_JENIS[self::JENIS_BERITA];
    }

    public function getIsKegiatanAttribute(): bool
    {
        return $this->jenis === self::JENIS_KEGIATAN;
    }

    public function getMediaSosialAttribute(): ?array
    {
        return filled($this->link_media_sosial)
            ? ProgramStudi::uraikanMediaSosial($this->link_media_sosial)
            : null;
    }

    public function getGambarUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->gambar);
    }

    public function getCuplikanAttribute(): string
    {
        return $this->ringkasan ?: Str::limit(trim(strip_tags((string) $this->isi)), 140);
    }

    public function getParagrafAttribute(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->isi))
            ->map(fn ($b) => trim($b))
            ->filter()
            ->values()
            ->all();
    }

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
