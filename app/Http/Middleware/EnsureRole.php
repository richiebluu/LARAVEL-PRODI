<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($roles && ! in_array($user->role, $roles, true)) {
            return redirect()->route(match ($user->role) {
                'mahasiswa' => 'mahasiswa-dashboard',
                'staff' => 'staff-dashboard',
                default => 'home',
            });
        }

        return $next($request);
    }
}
