<?php

use Illuminate\Support\Facades\Route;

// Named login route for Laravel's default authentication redirects
Route::get('/login', fn() => view('app'))->name('login');

// Catch-all route untuk Vue Router SPA - handle semua routes
Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');
