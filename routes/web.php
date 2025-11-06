<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\Auth\CustomAdminLoginController;
use App\Http\Controllers\Auth\CustomAdminRegisterController;

Route::get('/', function () {
    return redirect('/admin');
})->name('home');

// Custom Admin Login Routes
Route::get('/admin/login', [CustomAdminLoginController::class, 'showLoginForm'])->name('admin.custom.login.form');
Route::post('/admin/login', [CustomAdminLoginController::class, 'login'])->name('admin.custom.login');

// Custom Admin Register Routes
Route::get('/admin/register', [CustomAdminRegisterController::class, 'showRegisterForm'])
    ->middleware('guest')
    ->name('admin.custom.register.form');
Route::post('/admin/register', [CustomAdminRegisterController::class, 'register'])
    ->middleware('guest')
    ->name('admin.custom.register');

// Guest-accessible custom register outside Filament's /admin guard
Route::get('/signup', [CustomAdminRegisterController::class, 'showRegisterForm'])
    ->middleware('guest')
    ->name('custom.register.form');
Route::post('/signup', [CustomAdminRegisterController::class, 'register'])
    ->middleware('guest')
    ->name('custom.register');

// Filament route aliases
Route::get('/admin/login', [CustomAdminLoginController::class, 'showLoginForm'])->name('filament.admin.auth.login');
Route::post('/admin/logout', [CustomAdminLoginController::class, 'logout'])->name('filament.admin.auth.logout');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

require __DIR__.'/auth.php';
