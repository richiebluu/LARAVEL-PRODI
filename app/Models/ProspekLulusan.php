<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspekLulusan extends Model
{
    use HasFactory;

    protected $table = 'prospek_lulusan';

    protected $primaryKey = 'id_prospek_lulusan';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    public const IKON_BAWAAN = 'fa-briefcase';

    public const IKON = [
        'fa-briefcase' => 'Umum / Karier',
        'fa-code' => 'Pemrograman / Software',
        'fa-laptop-code' => 'Pengembangan Web',
        'fa-mobile-screen' => 'Aplikasi Mobile',
        'fa-network-wired' => 'Jaringan Komputer',
        'fa-server' => 'Server & Cloud',
        'fa-cloud' => 'Cloud Computing',
        'fa-shield-halved' => 'Keamanan Siber',
        'fa-user-secret' => 'Ethical Hacking',
        'fa-database' => 'Basis Data',
        'fa-chart-line' => 'Data & Analitik',
        'fa-brain' => 'Kecerdasan Buatan (AI)',
        'fa-robot' => 'AI, IoT & Robotika',
        'fa-microchip' => 'Perangkat Keras & Embedded',
        'fa-pen-ruler' => 'Desain UI/UX',
        'fa-palette' => 'Desain Grafis & Multimedia',
        'fa-gamepad' => 'Pengembangan Game',
        'fa-bug' => 'Software Testing / QA',
        'fa-gears' => 'DevOps & Otomasi',
        'fa-headset' => 'IT Support',
        'fa-diagram-project' => 'Manajemen Proyek TI',
        'fa-sitemap' => 'Analis Sistem',
        'fa-cart-shopping' => 'E-Commerce & Digital Marketing',
        'fa-chalkboard-user' => 'Pendidikan & Pelatihan',
        'fa-rocket' => 'Technopreneur / Startup',
    ];

    public const IKON_DESKRIPSI = [
        'fa-briefcase' => 'Biasanya digunakan untuk prospek karier umum, pekerjaan kantoran, atau profesi yang tidak masuk bidang khusus.',
        'fa-code' => 'Biasanya digunakan untuk hal yang berhubungan dengan pemrograman, programmer, software engineer, dan pengembang aplikasi.',
        'fa-laptop-code' => 'Biasanya digunakan untuk hal yang berhubungan dengan pembuatan website, seperti web developer, front-end, dan back-end.',
        'fa-mobile-screen' => 'Biasanya digunakan untuk hal yang berhubungan dengan aplikasi Android/iOS dan mobile developer.',
        'fa-network-wired' => 'Biasanya digunakan untuk hal yang berhubungan dengan jaringan komputer, network engineer, dan administrator jaringan.',
        'fa-server' => 'Biasanya digunakan untuk hal yang berhubungan dengan server, system administrator, dan pengelolaan infrastruktur.',
        'fa-cloud' => 'Biasanya digunakan untuk hal yang berhubungan dengan layanan cloud, cloud engineer, dan penyimpanan online.',
        'fa-shield-halved' => 'Biasanya digunakan untuk hal yang berhubungan dengan keamanan siber, keamanan data, dan cyber security analyst.',
        'fa-user-secret' => 'Biasanya digunakan untuk hal yang berhubungan dengan keamanan, hacking, penetration tester, dan ethical hacker.',
        'fa-database' => 'Biasanya digunakan untuk hal yang berhubungan dengan basis data, database administrator, dan pengelolaan data.',
        'fa-chart-line' => 'Biasanya digunakan untuk hal yang berhubungan dengan analisis data, data analyst, data scientist, dan laporan bisnis.',
        'fa-brain' => 'Biasanya digunakan untuk hal yang berhubungan dengan kecerdasan buatan, machine learning, dan AI engineer.',
        'fa-robot' => 'Biasanya digunakan untuk hal yang berhubungan dengan robotika, Internet of Things (IoT), dan otomasi perangkat.',
        'fa-microchip' => 'Biasanya digunakan untuk hal yang berhubungan dengan perangkat keras, sistem tertanam (embedded), dan teknisi komputer.',
        'fa-pen-ruler' => 'Biasanya digunakan untuk hal yang berhubungan dengan desain tampilan aplikasi, UI/UX designer, dan prototipe.',
        'fa-palette' => 'Biasanya digunakan untuk hal yang berhubungan dengan desain grafis, multimedia, animasi, dan konten kreatif.',
        'fa-gamepad' => 'Biasanya digunakan untuk hal yang berhubungan dengan pembuatan game, game developer, dan game designer.',
        'fa-bug' => 'Biasanya digunakan untuk hal yang berhubungan dengan pengujian aplikasi, software tester, dan quality assurance.',
        'fa-gears' => 'Biasanya digunakan untuk hal yang berhubungan dengan DevOps, otomasi proses, dan deployment aplikasi.',
        'fa-headset' => 'Biasanya digunakan untuk hal yang berhubungan dengan dukungan teknis, helpdesk, dan IT support.',
        'fa-diagram-project' => 'Biasanya digunakan untuk hal yang berhubungan dengan manajemen proyek TI, project manager, dan scrum master.',
        'fa-sitemap' => 'Biasanya digunakan untuk hal yang berhubungan dengan analisis dan perancangan sistem, seperti system analyst.',
        'fa-cart-shopping' => 'Biasanya digunakan untuk hal yang berhubungan dengan toko online, e-commerce, dan digital marketing.',
        'fa-chalkboard-user' => 'Biasanya digunakan untuk hal yang berhubungan dengan pendidikan, pengajar, trainer, dan instruktur TI.',
        'fa-rocket' => 'Biasanya digunakan untuk hal yang berhubungan dengan wirausaha teknologi, startup, dan technopreneur.',
    ];

    protected $fillable = [
        'staff_prodi_id',
        'nama',
        'ikon',
        'deskripsi',
        'status',
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
        return $query->orderBy('id_prospek_lulusan');
    }

    public static function normalisasiIkon(?string $ikon): ?string
    {
        $ikon = trim((string) $ikon);
        if ($ikon === '') {
            return null;
        }

        foreach (self::IKON as $kelas => $label) {
            if (mb_strtolower($label) === mb_strtolower($ikon)) {
                return $kelas;
            }
        }

        $gaya = 'fa-solid';
        $nama = null;
        $aliasGaya = ['fas' => 'fa-solid', 'far' => 'fa-regular', 'fab' => 'fa-brands'];

        foreach (preg_split('/\s+/', strtolower($ikon)) as $token) {
            $token = $aliasGaya[$token] ?? $token;
            if (in_array($token, ['fa-solid', 'fa-regular', 'fa-brands'], true)) {
                $gaya = $token;
            } elseif ($nama === null) {
                $nama = str_starts_with($token, 'fa-') ? $token : 'fa-'.$token;
            } else {
                return null;
            }
        }

        if ($nama === null || ! preg_match('/^fa-[a-z0-9]+(-[a-z0-9]+)*$/', $nama)) {
            return null;
        }

        return $gaya === 'fa-solid' ? $nama : $gaya.' '.$nama;
    }

    public function getNamaIkonAttribute(): string
    {
        $ikon = self::normalisasiIkon($this->ikon) ?? self::IKON_BAWAAN;

        return self::IKON[$ikon] ?? 'Ikon lainnya';
    }

    public function getKelasIkonAttribute(): string
    {
        $ikon = self::normalisasiIkon($this->ikon) ?? self::IKON_BAWAAN;

        return str_contains($ikon, ' ') ? $ikon : 'fa-solid '.$ikon;
    }
}
