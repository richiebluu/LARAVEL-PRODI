<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** ERD: primary key USERS = id_user. */
    protected $primaryKey = 'id_user';

    /**
     * Role yang dikenali sistem beserta labelnya di tampilan.
     * REVISI 26-09-2026: role Dosen ditiadakan. Aktor sistem: Admin/Staff Prodi,
     * Mahasiswa, dan Sistem (proses otomatis seperti perhitungan ranking SAW).
     * Data dosen tetap ada sebagai Data Master (tabel `dosen`), tanpa akun login.
     */
    public const ROLE = [
        'mahasiswa' => 'Mahasiswa',
        'staff' => 'Staff Prodi',
    ];

    /**
     * Kolom yang boleh diisi massal.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'google_id',
    ];

    /**
     * Kolom yang disembunyikan pada serialisasi.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** Relasi ke data mahasiswa (ERD: USERS 1 -- MEMILIKI -- 1 MAHASISWA, FK mahasiswa.user_id). */
    public function mahasiswa(): HasOne
    {
        return $this->hasOne(Mahasiswa::class, 'user_id', 'id_user');
    }

    /** Relasi ke data staff prodi (ERD: USERS 1 -- MEMILIKI -- 1 STAFF_PRODI). */
    public function staffProdi(): HasOne
    {
        return $this->hasOne(StaffProdi::class, 'id_user', 'id_user');
    }



    /** Helper role. */
    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /** Domain email institusi untuk role tertentu (config/auth.php -> domain_email). */
    public static function domainEmail(string $role): ?string
    {
        return config('auth.domain_email.'.$role);
    }

    /** Label role untuk tampilan (contoh: "Staff Prodi"). */
    public function getLabelRoleAttribute(): string
    {
        return self::ROLE[$this->role] ?? ucfirst((string) $this->role);
    }

    /** Domain (bagian setelah "@") dari sebuah email, huruf kecil. */
    public static function domainDari(?string $email): string
    {
        return strtolower((string) substr(strrchr((string) $email, '@') ?: '', 1));
    }

    /**
     * Jenis akun yang BOLEH dipakai sebuah email berdasarkan domain PERSIS
     * (bukan akhiran), mis. "@mhs.politala.ac.id" -> mahasiswa,
     * "@politala.ac.id" -> staff. Null bila domain tidak diizinkan.
     * Catatan: hasil ini bukan penentu akhir — akun tetap harus terdaftar di database.
     */
    public static function roleDariDomain(?string $email): ?string
    {
        $domain = self::domainDari($email);

        foreach (array_keys(self::ROLE) as $role) {
            if ($domain !== '' && $domain === strtolower((string) self::domainEmail($role))) {
                return $role;
            }
        }

        return null;
    }

    /** Apakah email memakai domain institusi yang sesuai dengan role. */
    public static function emailSesuaiRole(?string $email, string $role): bool
    {
        $domain = self::domainEmail($role);

        if (blank($email) || blank($domain)) {
            return false;
        }

        return self::domainDari($email) === strtolower($domain);
    }

    /**
     * Aturan validasi: email wajib memakai domain institusi sesuai role.
     * Dipakai pada form login, Login dengan Google, CRUD Staff Prodi, dan perubahan profil.
     */
    public static function aturanDomainEmail(string $role): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($role) {
            if (! self::emailSesuaiRole((string) $value, $role)) {
                $fail('Email '.(self::ROLE[$role] ?? $role).' wajib memakai domain @'.self::domainEmail($role).'.');
            }
        };
    }

    /**
     * Casting atribut.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
