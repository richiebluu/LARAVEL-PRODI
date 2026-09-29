<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PROSPEK LULUSAN (REVISI 28-09-2026).
 * Dikelola Staff Prodi. Tampil di Profil > Prospek Lulusan memakai card yang sama
 * dengan halaman Lowongan Kerja (ta-card); ikon kategori dipilih dari dropdown.
 */
class ProspekLulusan extends Model
{
    use HasFactory;

    protected $table = 'prospek_lulusan';

    /** ERD: primary key PROSPEK_LULUSAN = id_prospek_lulusan. */
    protected $primaryKey = 'id_prospek_lulusan';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    /** Pilihan ikon kategori (kelas Font Awesome 6 yang sudah dipakai website) => label dropdown. */
    public const IKON = [
        'fa-briefcase' => 'Umum / Karier',
        'fa-code' => 'Pemrograman / Software',
        'fa-laptop-code' => 'Pengembangan Web',
        'fa-mobile-screen' => 'Aplikasi Mobile',
        'fa-network-wired' => 'Jaringan Komputer',
        'fa-server' => 'Server & Cloud',
        'fa-shield-halved' => 'Keamanan Siber',
        'fa-database' => 'Basis Data',
        'fa-chart-line' => 'Data & Analitik',
        'fa-pen-ruler' => 'Desain UI/UX',
        'fa-robot' => 'AI, IoT & Robotika',
        'fa-headset' => 'IT Support',
        'fa-diagram-project' => 'Manajemen Proyek TI',
        'fa-chalkboard-user' => 'Pendidikan & Pelatihan',
        'fa-rocket' => 'Technopreneur / Startup',
    ];

    protected $fillable = [
        'staff_prodi_id',
        'nama',
        'kategori',
        'ikon',
        'deskripsi',
        'status',
    ];

    /** ERD: PROSPEK_LULUSAN (N) -- DIKELOLA --> STAFF_PRODI (1). */
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
        // ERD tidak memiliki kolom urutan: tampil sesuai urutan input (primary key).
        return $query->orderBy('id_prospek_lulusan');
    }

    /** Kelas ikon yang aman dipakai (kembali ke ikon umum bila tidak dikenal). */
    public function getKelasIkonAttribute(): string
    {
        return array_key_exists((string) $this->ikon, self::IKON) ? $this->ikon : 'fa-briefcase';
    }
}
