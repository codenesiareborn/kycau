<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>KYCAU.ID - Admin Login</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
          crossorigin=""/>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        #map {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* Overlay */
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.8), rgba(147, 51, 234, 0.8));
            z-index: 2;
        }

        /* Logo */
        .logo {
            position: absolute;
            top: 30px;
            left: 30px;
            font-size: 28px;
            font-weight: 700;
            color: white;
            z-index: 10;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Login Container */
        .login-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            width: 100%;
            max-width: 400px;
            padding: 0 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px 35px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: #6b7280;
            font-size: 14px;
        }

        /* Filament Form Styles */
        .fi-form {
            width: 100%;
        }

        .fi-fo-field-wrp {
            margin-bottom: 20px;
        }

        .fi-fo-field-wrp-label {
            margin-bottom: 8px;
        }

        .fi-fo-field-wrp-label .fi-fo-field-wrp-label-text {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .fi-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .fi-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .fi-checkbox {
            margin-right: 8px;
        }

        .fi-checkbox-label {
            font-size: 14px;
            color: #374151;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #3b82f6;
        }

        .remember-container {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .remember-checkbox {
            margin-right: 8px;
            width: 16px;
            height: 16px;
        }

        .remember-label {
            font-size: 14px;
            color: #6b7280;
            cursor: pointer;
        }

        .login-button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .security-notice {
            margin-top: 20px;
            padding: 12px;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            border-radius: 8px;
            text-align: center;
            font-size: 12px;
            color: #059669;
        }

        .error-message {
            color: #dc2626;
            font-size: 12px;
            margin-top: 5px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .logo {
                top: 20px;
                left: 20px;
                font-size: 24px;
            }
            
            .login-card {
                padding: 30px 25px;
                margin: 0 15px;
            }
            
            .login-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Map Container -->
    <div id="map"></div>
    
    <!-- Overlay -->
    <div class="overlay"></div>
    
    <!-- Logo -->
    <div class="logo">
        KYCAU.ID
    </div>
    
    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">Admin Dashboard</h1>
                <p class="login-subtitle">Secure access to your control panel</p>
            </div>
            
            <form wire:submit="authenticate" class="space-y-6">
                {{ $this->form }}
                
                <div class="form-group">
                    <button type="submit" class="login-button" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>
                            Access Dashboard
                        </span>
                        <span wire:loading>
                            <i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>
                            Authenticating...
                        </span>
                    </button>
                </div>
                
                <div class="security-notice">
                    <i class="fas fa-shield-alt" style="margin-right: 5px;"></i>
                    Your connection is secured with SSL encryption
                </div>
            </form>
        </div>
    </div>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
            crossorigin=""></script>
    
    <script>
        // Initialize map in completely isolated scope
        (function() {
            'use strict';
            
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize map
                const mapInstance = L.map('map', {
                    center: [-6.2088, 106.8456], // Jakarta coordinates
                    zoom: 11,
                    zoomControl: false,
                    attributionControl: false
                });

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(mapInstance);

                // Jakarta locations for smooth animation
                const jakartaLocations = [
                    [-6.2088, 106.8456], // Central Jakarta
                    [-6.1751, 106.8650], // North Jakarta
                    [-6.2615, 106.7809], // West Jakarta
                    [-6.2297, 106.9758], // East Jakarta
                    [-6.3407, 106.8548]  // South Jakarta
                ];

                let centerIndex = 0;

                // Smooth map center animation
                setInterval(function() {
                    centerIndex = (centerIndex + 1) % jakartaLocations.length;
                    mapInstance.flyTo(jakartaLocations[centerIndex], 11, {
                        duration: 3
                    });
                }, 8000);
            });
            
            // Password toggle function in isolated scope
            window.togglePassword = function() {
                const passwordInput = document.getElementById('password');
                const toggleIcon = document.querySelector('.password-toggle');
                
                if (passwordInput && toggleIcon) {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        toggleIcon.classList.remove('fa-eye');
                        toggleIcon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        toggleIcon.classList.remove('fa-eye-slash');
                        toggleIcon.classList.add('fa-eye');
                    }
                }
            };
        })();
    </script>
</body>
</html>