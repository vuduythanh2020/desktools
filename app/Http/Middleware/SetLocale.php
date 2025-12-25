<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');
        if (!in_array($locale, ['vi', 'en'], true)) {
            $locale = 'vi';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
