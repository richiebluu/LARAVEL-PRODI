<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'id_user';

    public const ROLE = [
        'mahasiswa' => 'Mahasiswa',
        'staff' => 'Staff Prodi',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function mahasiswa(): HasOne
    {
        return $this->hasOne(Mahasiswa::class, 'user_id', 'id_user');
    }

    public function staffProdi(): HasOne
    {
        return $this->hasOne(StaffProdi::class, 'id_user', 'id_user');
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public static function domainEmail(string $role): ?string
    {
        return config('auth.domain_email.'.$role);
    }

    public function getLabelRoleAttribute(): string
    {
        return self::ROLE[$this->role] ?? ucfirst((string) $this->role);
    }

    public static function domainDari(?string $email): string
    {
        return strtolower((string) substr(strrchr((string) $email, '@') ?: '', 1));
    }

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

    public static function emailSesuaiRole(?string $email, string $role): bool
    {
        $domain = self::domainEmail($role);

        if (blank($email) || blank($domain)) {
            return false;
        }

        return self::domainDari($email) === strtolower($domain);
    }

    public static function aturanDomainEmail(string $role): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($role) {
            if (! self::emailSesuaiRole((string) $value, $role)) {
                $fail('Email '.(self::ROLE[$role] ?? $role).' wajib memakai domain @'.self::domainEmail($role).'.');
            }
        };
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
