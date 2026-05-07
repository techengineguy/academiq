<?php

use Illuminate\Support\Facades\Route;

Route::domain(config('domain.main'))->group(function () {

    // Welcome / Home page
    Route::view('/', 'welcome')->name('home');
    Route::livewire('admissions/apply/{institution:uuid}', 'pages::public.admissions.apply')
        ->name('admissions.apply');
    Route::livewire('admissions/success/{institution:uuid}/{application:uuid}', 'pages::public.admissions.success')
        ->name('admissions.success');
});

require __DIR__.'/auth.php';
require __DIR__.'/app.php';
require __DIR__.'/settings.php';