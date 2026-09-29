<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Halaman login. Jika sudah login, langsung ke dashboard sesuai role. */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route($this->rute(Auth::user()->role));
        }

        return view('login');
    }

    /**
     * Proses login memakai session/authentication Laravel.
     *
     * REVISI 24-09-2026 (diperbarui 26-09-2026: role Dosen ditiadakan):
     *  1. Pengguna memilih jenis akun (Mahasiswa / Staff Prodi).
     *  2. Email wajib memakai domain institusi Politala sesuai role
     *     (config/auth.php -> domain_email).
     *  3. Role akun di database harus sama dengan role yang dipilih.
     * Akses dashboard tetap dijaga middleware role (EnsureRole) di backend.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(User::ROLE))],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'role.required' => 'Silakan pilih jenis akun (Mahasiswa atau Staff Prodi).',
            'role.in' => 'Jenis akun tidak dikenali.',
        ], [
            'role' => 'Jenis akun',
            'email' => 'Email',
            'password' => 'Password',
        ]);

        $role = $data['role'];
        $label = User::ROLE[$role];

        if (! User::emailSesuaiRole($data['email'], $role)) {
            throw ValidationException::withMessages([
                'email' => 'Akun '.$label.' wajib memakai email institusi @'.User::domainEmail($role).'.',
            ]);
        }

        $remember = $request->boolean('remember');

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak sesuai.',
            ]);
        }

        $user = Auth::user();

        if ($user->role !== $role) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun ini bukan akun '.$label.'. Silakan pilih jenis akun yang sesuai.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($this->rute($user->role)));
    }

    /** Logout dan hancurkan session. */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function rute(string $role): string
    {
        return match ($role) {
            'mahasiswa' => 'mahasiswa-dashboard',
            'staff' => 'staff-dashboard',
            default => 'home',
        };
    }
}
