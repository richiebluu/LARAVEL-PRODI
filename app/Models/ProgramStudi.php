<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramStudi extends Model
{
    use HasFactory;

    protected $table = 'program_studi';

    protected $primaryKey = 'id_program_studi';

    protected $fillable = [
        'staff_prodi_id',
        'nama_prodi',
        'deskripsi',
        'visi',
        'misi',
        'jumlah_alumni',
        'jumlah_dosen',
        'link_akamawa',
        'kode_etik',
        'link_media_sosial',
    ];

    public const LINK_AKAMAWA_BAWAAN = 'https://akamawa.politala.ac.id/';

    protected $casts = [
        'jumlah_alumni' => 'integer',
        'jumlah_dosen' => 'integer',
    ];

    public const PLATFORM_MEDIA_SOSIAL = [
        'instagram.com' => ['label' => 'Instagram', 'ikon' => 'fa-brands fa-instagram'],
        'facebook.com' => ['label' => 'Facebook', 'ikon' => 'fa-brands fa-facebook-f'],
        'fb.com' => ['label' => 'Facebook', 'ikon' => 'fa-brands fa-facebook-f'],
        'tiktok.com' => ['label' => 'TikTok', 'ikon' => 'fa-brands fa-tiktok'],
        'youtube.com' => ['label' => 'YouTube', 'ikon' => 'fa-brands fa-youtube'],
        'youtu.be' => ['label' => 'YouTube', 'ikon' => 'fa-brands fa-youtube'],
        'x.com' => ['label' => 'X', 'ikon' => 'fa-brands fa-x-twitter'],
        'twitter.com' => ['label' => 'X', 'ikon' => 'fa-brands fa-x-twitter'],
        'linkedin.com' => ['label' => 'LinkedIn', 'ikon' => 'fa-brands fa-linkedin-in'],
        'wa.me' => ['label' => 'WhatsApp', 'ikon' => 'fa-brands fa-whatsapp'],
        'whatsapp.com' => ['label' => 'WhatsApp', 'ikon' => 'fa-brands fa-whatsapp'],
        't.me' => ['label' => 'Telegram', 'ikon' => 'fa-brands fa-telegram'],
        'threads.net' => ['label' => 'Threads', 'ikon' => 'fa-brands fa-threads'],
        'threads.com' => ['label' => 'Threads', 'ikon' => 'fa-brands fa-threads'],
    ];

    private const SEGMEN_BUKAN_AKUN = ['channel', 'c', 'user', 'company', 'school', 'in', 'pages', 'groups', 'p', 'reel', 'video', 'watch', 'profile.php'];

    public function strukturOrganisasi(): HasMany
    {
        return $this->hasMany(StrukturOrganisasi::class, 'program_studi_id', 'id_program_studi')->urut();
    }

    public function mataKuliah(): HasMany
    {
        return $this->hasMany(MataKuliah::class, 'program_studi_id', 'id_program_studi');
    }

    public function getUrlAkamawaAttribute(): string
    {
        return $this->link_akamawa ?: self::LINK_AKAMAWA_BAWAAN;
    }

    public function getKodeEtikUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->kode_etik);
    }

    public function akreditasi(): HasMany
    {
        return $this->hasMany(Akreditasi::class, 'program_studi_id', 'id_program_studi');
    }

    public function akreditasiTerbaru(): HasMany
    {
        return $this->hasMany(Akreditasi::class, 'program_studi_id', 'id_program_studi')->terbaru();
    }

    public function akreditasiBerlaku(): HasMany
    {
        return $this->hasMany(Akreditasi::class, 'program_studi_id', 'id_program_studi')->terakreditasi()->terbaru();
    }

    public function staffProdi(): BelongsTo
    {
        return $this->belongsTo(StaffProdi::class, 'staff_prodi_id', 'id_staff_prodi');
    }

    public function getLinkMediaSosialListAttribute(): array
    {
        return $this->pecahBaris($this->link_media_sosial);
    }

    public function getMediaSosialAttribute(): array
    {
        return collect($this->link_media_sosial_list)
            ->map(fn (string $url) => self::uraikanMediaSosial($url))
            ->all();
    }

    public static function mediaSosialWebsite(): array
    {
        $dariDatabase = static::query()->value('link_media_sosial');

        if (filled($dariDatabase)) {
            return (new static(['link_media_sosial' => $dariDatabase]))->media_sosial;
        }

        return array_values(config('prodi.sosial_media', []));
    }

    public static function uraikanMediaSosial(string $url): array
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^(www\.|m\.|web\.|vm\.|vt\.)/', '', $host);

        $platform = ['label' => 'Media Sosial', 'ikon' => 'fa-solid fa-globe'];
        foreach (self::PLATFORM_MEDIA_SOSIAL as $domain => $data) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                $platform = $data;
                break;
            }
        }

        $segmen = collect(explode('/', trim((string) parse_url($url, PHP_URL_PATH), '/')))
            ->filter(fn ($s) => $s !== '' && ! in_array(strtolower($s), self::SEGMEN_BUKAN_AKUN, true))
            ->first();

        return [
            'url' => $url,
            'label' => $platform['label'],
            'ikon' => $platform['ikon'],
            'nama_akun' => $segmen ? '@'.ltrim(urldecode($segmen), '@') : ($host ?: $url),
        ];
    }

    public function getMisiListAttribute(): array
    {
        return $this->pecahBaris($this->misi);
    }

    private function pecahBaris(?string $teks): array
    {
        if (blank($teks)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $teks))
            ->map(fn ($baris) => trim($baris))
            ->filter()
            ->values()
            ->all();
    }
}
