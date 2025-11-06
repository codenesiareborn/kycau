<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

        return view('auth.custom-admin-register');
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
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
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
            ->with('status', 'Registration successful. Please check your email to verify your account and then log in.');
    }
}