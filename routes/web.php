<?php

use Illuminate\Support\Facades\Route;

// Catch-all route untuk Vue Router SPA - handle semua routes
Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');
