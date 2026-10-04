<?php

namespace App\Services;

use App\Exceptions\GoogleLoginDitolak;
use App\Models\User;

class GoogleLoginService
{
    public function cariAkun(?string $email, bool $emailTerverifikasi, ?string $googleId): User
    {
        $email = strtolower(trim((string) $email));

        if ($email === '' || ! $emailTerverifikasi || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new GoogleLoginDitolak(GoogleLoginDitolak::PESAN_UMUM);
        }

        $role = User::roleDariDomain($email);

        if ($role === null) {
            throw new GoogleLoginDitolak(GoogleLoginDitolak::PESAN_UMUM);
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('role', $role)
            ->first();

        $terdaftar = match ($role) {
            'mahasiswa' => $user?->mahasiswa !== null,
            'staff' => $user?->staffProdi !== null,
            default => false,
        };

        if (! $user || ! $terdaftar) {
            throw new GoogleLoginDitolak(GoogleLoginDitolak::PESAN_UMUM);
        }

        if ($googleId !== null && $googleId !== '') {
            if ($user->google_id && $user->google_id !== $googleId) {
                throw new GoogleLoginDitolak('Akun ini sudah terhubung dengan akun Google lain. Hubungi Staff Prodi.');
            }

            if (! $user->google_id) {
                $user->forceFill(['google_id' => $googleId])->save();
            }
        }

        return $user;
    }
}
