<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleLoginDitolak;
use App\Services\GoogleLoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function __construct(private readonly GoogleLoginService $google) {}

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
        return redirect()->route('login')->withErrors(['email' => __($pesan)]);
    }
}
