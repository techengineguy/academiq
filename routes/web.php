<?php

use Illuminate\Support\Facades\Route;

Route::domain(config('domain.main'))->group(function () {

    // Welcome / Home page
    Route::view('/', 'welcome')->name('home');
});

require __DIR__.'/auth.php';
require __DIR__.'/app.php';
require __DIR__.'/settings.php';