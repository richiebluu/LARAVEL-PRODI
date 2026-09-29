<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * KEGIATAN MAHASISWA PROGRAM STUDI (REVISI 28-09-2026 tahap 2).
 * Dikelola Staff Prodi, tampil di menu Mahasiswa > Kegiatan Mahasiswa (urutan ketiga).
 */
class KegiatanMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'kegiatan_mahasiswa';

    /** ERD: primary key KEGIATAN_MAHASISWA = id_kegiatan_mahasiswa. */
    protected $primaryKey = 'id_kegiatan_mahasiswa';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    /** Kategori kegiatan => ikon Font Awesome. */
    public const KATEGORI = [
        'Seminar & Workshop' => 'fa-chalkboard-user',
        'Lomba & Kompetisi' => 'fa-trophy',
        'Pengabdian Masyarakat' => 'fa-hand-holding-heart',
        'Kunjungan Industri' => 'fa-industry',
        'Organisasi & Kepanitiaan' => 'fa-people-group',
        'Pelatihan & Sertifikasi' => 'fa-certificate',
        'Lainnya' => 'fa-calendar-check',
    ];

    protected $fillable = [
        'staff_prodi_id',
        'judul',
        'kategori',
        'tanggal',
        'lokasi',
        'penyelenggara',
        'deskripsi',
        'foto',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /** KEGIATAN_MAHASISWA (N) -- DIKELOLA --> STAFF_PRODI (1). */
    public function staffProdi(): BelongsTo
    {
        return $this->belongsTo(StaffProdi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $query;
        }

        $kata = '%'.$kata.'%';

        return $query->where(fn (Builder $q) => $q->where('judul', 'like', $kata)
            ->orWhere('lokasi', 'like', $kata)
            ->orWhere('penyelenggara', 'like', $kata));
    }

    public function getFotoUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->foto);
    }

    public function getIkonAttribute(): string
    {
        return self::KATEGORI[$this->kategori] ?? 'fa-calendar-check';
    }

    public function getCuplikanAttribute(): string
    {
        return Str::limit(trim((string) $this->deskripsi), 130);
    }

    /** Paragraf deskripsi (dipisah baris baru). */
    public function getParagrafAttribute(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->deskripsi))
            ->map(fn ($b) => trim($b))
            ->filter()
            ->values()
            ->all();
    }
}
