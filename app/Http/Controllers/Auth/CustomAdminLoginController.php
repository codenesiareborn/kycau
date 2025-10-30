<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class CustomAdminLoginController extends Controller
{
    /**
     * Show the custom admin login form
     */
    public function showLoginForm()
    {
        // Redirect if already authenticated
        if (Auth::check()) {
            return redirect('/admin');
        }
        
        return view('auth.custom-admin-login');
    }

    /**
     * Handle admin login with Filament compatibility
     */
    public function login(Request $request)
    {
        // Rate limiting
        $this->ensureIsNotRateLimited($request);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            // Clear rate limiter
            RateLimiter::clear($this->throttleKey($request));
            
            // Regenerate session for security
            $request->session()->regenerate();
            
            // Ensure Filament session compatibility
            $this->ensureFilamentCompatibility($request);
            
            // Redirect to Filament admin panel with proper intended URL handling
            $intendedUrl = $request->session()->get('url.intended', '/admin');
            
            // Ensure we're redirecting to admin panel
            if (!str_starts_with($intendedUrl, '/admin')) {
                $intendedUrl = '/admin';
            }
            
            return redirect($intendedUrl);
        }

        // Hit rate limiter
        RateLimiter::hit($this->throttleKey($request));

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        // Store the current session token before logout
        $token = $request->session()->token();
        
        Auth::logout();
        
        // Invalidate session but regenerate token first to avoid CSRF issues
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Clear any Filament-specific session data
        $request->session()->forget('filament_admin_authenticated');
        $request->session()->forget('filament_admin_user_id');
        
        return redirect('/admin/login')->with('status', 'You have been logged out successfully.');
    }

    /**
     * Ensure the login request is not rate limited.
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
    }

    /**
     * Ensure compatibility with Filament authentication
     */
    protected function ensureFilamentCompatibility(Request $request): void
    {
        // Set any additional session data that Filament might need
        // This ensures the user is properly authenticated for Filament
        $user = Auth::user();
        
        if ($user) {
            // Store user info in session for Filament compatibility
            $request->session()->put('filament_admin_authenticated', true);
            $request->session()->put('filament_admin_user_id', $user->id);
        }
    }
}
