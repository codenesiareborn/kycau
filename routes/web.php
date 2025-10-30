<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\Auth\CustomAdminLoginController;

Route::get('/', function () {
    return redirect('/admin');
})->name('home');

// Custom Admin Login Routes
Route::get('/admin/login', [CustomAdminLoginController::class, 'showLoginForm'])->name('admin.custom.login.form');
Route::post('/admin/login', [CustomAdminLoginController::class, 'login'])->name('admin.custom.login');

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
