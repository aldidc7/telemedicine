<?php

use Illuminate\Support\Facades\Route;

// Public routes untuk static pages
Route::get('/privacy-policy', fn() => view('pages.privacy-policy'))->name('privacy-policy');
Route::get('/terms-of-service', fn() => view('pages.terms-of-service'))->name('terms-of-service');

// Named login route for Laravel's default authentication redirects
Route::get('/login', fn() => view('app'))->name('login');

// Catch-all route untuk Vue Router SPA - handle semua routes
Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');

