<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\SsprPasswordReset;
use App\Livewire\Profile\Dashboard;
use App\Livewire\Profile\EditContact;
use App\Livewire\SetupWizard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/setup', SetupWizard::class)->name('setup');
Route::get('/password-reset', SsprPasswordReset::class)->name('sspr.password.request');
Route::get('/login', Login::class)->middleware('guest')->name('login');
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/', Dashboard::class)->name('home');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/profile/contact', EditContact::class)->name('profile.contact');
});
