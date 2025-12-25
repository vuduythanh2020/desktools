<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Sitemap: ' . url('/sitemap.xml'),
    ];

    return Response::make(implode("\n", $lines), 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
});

Route::get('/sitemap.xml', function () {
    $locales = ['vi', 'en'];
    $paths = ['', 'text-unescape', 'csv-cleaner'];
    $urls = [];
    $base = rtrim(config('app.url') ?: url('/'), '/');
    if (str_starts_with($base, 'http://')) {
        $base = 'https://' . substr($base, 7);
    }

    foreach ($locales as $locale) {
        foreach ($paths as $path) {
            $urls[] = $base . '/' . $locale . ($path === '' ? '' : '/' . $path);
        }
    }

    $lastmod = now()->toAtomString();
    $xml = ['<?xml version="1.0" encoding="UTF-8"?>'];
    $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach ($urls as $loc) {
        $xml[] = '  <url>';
        $xml[] = '    <loc>' . e($loc) . '</loc>';
        $xml[] = '    <lastmod>' . $lastmod . '</lastmod>';
        $xml[] = '  </url>';
    }

    $xml[] = '</urlset>';

    return Response::make(implode("\n", $xml), 200, [
        'Content-Type' => 'application/xml; charset=UTF-8',
    ]);
});

Route::get('/', function () {
    return redirect('/vi');
});

Route::prefix('{locale}')
    ->whereIn('locale', ['vi', 'en'])
    ->middleware('locale')
    ->group(function () {
        Route::get('/', function () {
            return view('home', [
                'locale' => app()->getLocale(),
            ]);
        })->name('home');

        Route::get('/text-unescape', function () {
            return view('tools.text-unescape', [
                'locale' => app()->getLocale(),
            ]);
        })->name('text-unescape');

        Route::get('/csv-cleaner', function () {
            return view('tools.csv-cleaner', [
                'locale' => app()->getLocale(),
            ]);
        })->name('csv-cleaner');
    });
