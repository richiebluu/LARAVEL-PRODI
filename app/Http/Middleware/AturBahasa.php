<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class AturBahasa
{
    public const BAHASA = ['id', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $bahasa = $request->session()->get('bahasa');
        $halamanDashboard = collect($request->route()?->gatherMiddleware() ?? [])
            ->contains(fn ($m) => is_string($m) && str_starts_with($m, 'role:'));

        if (in_array($bahasa, self::BAHASA, true) && ! $halamanDashboard) {
            App::setLocale($bahasa);
        }

        return $next($request);
    }
}
