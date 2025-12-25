<?php

use Illuminate\Support\Facades\Route;

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
