<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route($this->rute(Auth::user()->role));
        }

        return view('login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(User::ROLE))],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'role.required' => __('Silakan pilih jenis akun (Mahasiswa atau Staff Prodi).'),
            'role.in' => __('Jenis akun tidak dikenali.'),
        ], [
            'role' => __('Jenis akun'),
            'email' => 'Email',
            'password' => 'Password',
        ]);

        $role = $data['role'];
        $label = __(User::ROLE[$role]);

        if (! User::emailSesuaiRole($data['email'], $role)) {
            throw ValidationException::withMessages([
                'email' => __('Akun :akun wajib memakai email institusi @:domain.', ['akun' => $label, 'domain' => User::domainEmail($role)]),
            ]);
        }

        $remember = $request->boolean('remember');

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $remember)) {
            throw ValidationException::withMessages([
                'email' => __('Email atau password tidak sesuai.'),
            ]);
        }

        $user = Auth::user();

        if ($user->role !== $role) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('Akun ini bukan akun :akun. Silakan pilih jenis akun yang sesuai.', ['akun' => $label]),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($this->rute($user->role)));
    }

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
