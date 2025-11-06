<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFilamentCompatibility
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is authenticated and accessing Filament admin
        if (Auth::check() && $request->is('admin*')) {
            $user = Auth::user();
            
            // Ensure session data is properly set for Filament
            if (!$request->session()->has('filament_admin_authenticated')) {
                $request->session()->put('filament_admin_authenticated', true);
                $request->session()->put('filament_admin_user_id', $user->id);
                $request->session()->put('filament_admin_user_email', $user->email);
            }
        }

        return $next($request);
    }
}