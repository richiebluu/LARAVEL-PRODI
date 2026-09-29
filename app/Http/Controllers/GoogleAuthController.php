<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleLoginDitolak;
use App\Services\GoogleLoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/**
 * LOGIN DENGAN GOOGLE (REVISI 26-09-2026) — Google OAuth 2.0 via Laravel Socialite.
 *
 * Kredensial TIDAK ditulis di kode; diambil dari .env melalui config/services.php:
 *   GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT_URI
 * Selama paket laravel/socialite belum terpasang atau kredensial masih kosong,
 * tombol tetap tampil tetapi pengguna dikembalikan ke halaman login dengan pesan yang jelas.
 */
class GoogleAuthController extends Controller
{
    public function __construct(private readonly GoogleLoginService $google) {}

    /** Langkah 1: arahkan pengguna ke halaman persetujuan Google. */
    public function redirect()
    {
        if ($pesan = $this->belumSiap()) {
            return $this->kembali($pesan);
        }

        return Socialite::driver('google')
            ->redirectUrl($this->redirectUri())
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /** Langkah 2: Google mengembalikan pengguna ke /auth/google/callback. */
    public function callback(Request $request)
    {
        if ($pesan = $this->belumSiap()) {
            return $this->kembali($pesan);
        }

        if ($request->filled('error')) {
            return $this->kembali('Login dengan Google dibatalkan.');
        }

        try {
            $akunGoogle = Socialite::driver('google')->redirectUrl($this->redirectUri())->user();
        } catch (\Throwable $e) {
            report($e);

            return $this->kembali('Login dengan Google gagal diproses. Silakan coba lagi.');
        }

        $raw = (array) $akunGoogle->getRaw();
        $terverifikasi = filter_var($raw['email_verified'] ?? $raw['verified_email'] ?? false, FILTER_VALIDATE_BOOLEAN);

        try {
            $user = $this->google->cariAkun($akunGoogle->getEmail(), $terverifikasi, (string) $akunGoogle->getId());
        } catch (GoogleLoginDitolak $e) {
            return $this->kembali($e->getMessage());
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route($user->role === 'staff' ? 'staff-dashboard' : 'mahasiswa-dashboard'));
    }

    /** Pesan bila integrasi belum dapat dipakai; null bila siap. */
    private function belumSiap(): ?string
    {
        if (! class_exists(Socialite::class)) {
            return 'Login dengan Google belum aktif: paket laravel/socialite belum terpasang (jalankan "composer require laravel/socialite").';
        }

        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return 'Login dengan Google belum dikonfigurasi: isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET pada file .env.';
        }

        return null;
    }

    private function redirectUri(): string
    {
        return config('services.google.redirect') ?: route('login.google.callback');
    }

    private function kembali(string $pesan)
    {
        return redirect()->route('login')->withErrors(['email' => $pesan]);
    }
}
