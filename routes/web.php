<?php

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'auth.session', 'verified', 'locale.sync.appsidebar'])->group(function () {
    Route::get('dashboard', function () {
        syncLangFiles([
            'components/timed-greeting',
            'pages/dashboard',
        ]);

        return Inertia::render('dashboard');
    })->name('dashboard');
});

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, config('app.supported_locales', ['en']))) {
        Cookie::queue(config('app.locale_cookie'), $locale, 60 * 24 * 30);
    }

    return back();
})->name('locale.switch');

require __DIR__.'/settings.php';
