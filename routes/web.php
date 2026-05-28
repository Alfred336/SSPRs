<?php

use App\Livewire\Auth\SsprPasswordReset;
use App\Livewire\SetupWizard;
use Illuminate\Support\Facades\Route;

Route::get('/setup', SetupWizard::class)->name('setup');
Route::get('/password-reset', SsprPasswordReset::class)->name('sspr.password.request');
Route::view('/login', 'auth.login')->name('login');

Route::view('/', 'welcome')->name('home');
