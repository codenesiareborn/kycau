<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class CustomAdminRegisterController extends Controller
{
    /**
     * Show the custom admin register form.
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect('/admin');
        }

        $packages = Package::orderBy('sort_order')->get();
        $defaultPackage = Package::where('is_trial', true)->where('is_active', true)->first();

        return view('auth.custom-admin-register', compact('packages', 'defaultPackage'));
    }

    /**
     * Handle the incoming registration request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
            'package_id' => ['nullable', 'exists:packages,id'],
        ]);

        // Get the selected package or default to trial
        $package = null;
        if (!empty($validated['package_id'])) {
            $package = Package::where('id', $validated['package_id'])
                ->where('is_active', true)
                ->first();
        }

        // Fallback to trial package if no valid package selected
        if (!$package) {
            $package = Package::where('is_trial', true)->where('is_active', true)->first();
        }

        // Calculate package expiration
        $packageExpiresAt = null;
        if ($package && $package->duration_days) {
            $packageExpiresAt = now()->addDays($package->duration_days);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'package_id' => $package?->id,
            'package_expires_at' => $packageExpiresAt,
        ]);

        // Optionally send verification email if enabled in the model / features
        if (method_exists($user, 'sendEmailVerificationNotification')) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                // Fail silently for now; verification can be triggered later
            }
        }

        return redirect()->route('login')
            ->with('status', 'Pendaftaran berhasil! Silakan cek email Anda untuk verifikasi akun, lalu login.');
    }
}