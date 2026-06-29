<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        syncLangFiles([
            'components/timed-greeting',
            'pages/dashboard',
        ]);

        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
