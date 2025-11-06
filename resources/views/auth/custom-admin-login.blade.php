<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>KYCAU.ID - Admin Login</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
          crossorigin=""/>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Map Container */
        #map {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* Logo */
        .logo {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #2d5a3d;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Login Container */
        .login-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            pointer-events: none; /* Allow map interactions to pass through */
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 25px 22px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.6s ease-out, floatingCard 4s ease-in-out infinite 1s;
            max-width: 308px;
            width: 90%;
            pointer-events: auto; /* Enable interactions on the login card */
        }

        /* Animations */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatingCard {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-6px);
            }
        }

        @keyframes bounceMarker {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0px) scale(1);
            }
            40% {
                transform: translateY(-8px) scale(1.05);
            }
            60% {
                transform: translateY(-4px) scale(1.02);
            }
        }

        @keyframes pulseMarker {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }

        @keyframes shakeMarker {
            0%, 100% {
                transform: translateX(0px);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-2px);
            }
            20%, 40%, 60%, 80% {
                transform: translateX(2px);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .login-subtitle {
            font-size: 12px;
            color: #6b7280;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 3px;
        }

        .form-input {
            width: 100%;
            padding: 9px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13px;
            background: white;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #2d5a3d;
            box-shadow: 0 0 0 3px rgba(45, 90, 61, 0.1);
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 14px;
            height: 14px;
            accent-color: #2d5a3d;
        }

        .checkbox-label {
            font-size: 12px;
            color: #6b7280;
        }

        .forgot-link {
            font-size: 12px;
            color: #2d5a3d;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            background: #2d5a3d;
            color: white;
            border: none;
            padding: 11px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 18px;
        }

        .login-button:hover {
            background: #1f3d2a;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(45, 90, 61, 0.3);
        }

        .divider {
            text-align: center;
            margin: 16px 0;
            position: relative;
            color: #9ca3af;
            font-size: 12px;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
            z-index: -1;
        }

        .divider span {
            background: white;
            padding: 0 12px;
        }

        .social-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }

        .social-button {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: white;
            color: #374151;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .social-button:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        /* Marker Animation Classes - Using child element approach */
        .marker-bounce .custom-marker-inner {
            animation: bounceMarker 2s ease-in-out infinite;
        }

        .marker-pulse .custom-marker-inner {
            animation: pulseMarker 2.5s ease-in-out infinite;
        }

        .marker-shake .custom-marker-inner {
            animation: shakeMarker 2s ease-in-out infinite;
        }

        .marker-bounce-delayed .custom-marker-inner {
            animation: bounceMarker 2.2s ease-in-out infinite 0.3s;
        }

        .marker-pulse-delayed .custom-marker-inner {
            animation: pulseMarker 2.8s ease-in-out infinite 0.6s;
        }

        .marker-shake-delayed .custom-marker-inner {
            animation: shakeMarker 2.3s ease-in-out infinite 0.9s;
        }

        /* Custom marker styling - NO transform on main container */
        .leaflet-marker-icon {
            transition: filter 0.3s ease;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }

        .leaflet-marker-icon:hover {
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.5));
        }

        /* Enhanced marker container - NO positioning transforms */
        .custom-marker {
            border-radius: 50%;
            overflow: visible;
            position: relative;
        }

        .custom-marker-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            position: relative;
            transform-origin: center center;
        }

        .signup-link {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }

        .signup-link a {
            color: #2d5a3d;
            text-decoration: none;
            font-weight: 500;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                left: 50%;
                transform: translate(-50%, -50%);
                max-width: 350px;
                padding: 0 20px;
            }
            
            .logo {
                top: 15px;
                left: 15px;
                font-size: 14px;
                padding: 6px 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Map Container -->
    <div id="map"></div>
    
    <!-- Logo -->
    <div class="logo">
        <i class="fas fa-database"></i> KYCAU.ID
    </div>
    
    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">Welcome Back!</h1>
                <p class="login-subtitle">Access your business intelligence dashboard</p>
            </div>
            
            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif
            
            <form method="POST" action="{{ route('admin.custom.login') }}">
                @csrf
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input" 
                           placeholder="Enter your email"
                           value="{{ old('email') }}" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input" 
                           placeholder="Enter your password"
                           required>
                </div>
                
                <div class="form-row">
                    <div class="checkbox-group">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember" class="checkbox-label">Remember me</label>
                    </div>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>
                
                <button type="submit" class="login-button">
                    Access Dashboard
                </button>
            </form>
            
            <div class="divider">
                <span>Or continue with</span>
            </div>
            
            <div class="social-buttons">
                <a href="#" class="social-button">
                    <i class="fab fa-google"></i>
                    Google
                </a>
                <a href="#" class="social-button">
                    <i class="fab fa-facebook-f"></i>
                    Facebook
                </a>
            </div>
            
            <div class="signup-link">
                Don't have an account? <a href="{{ route('custom.register.form') }}">Create one</a>
            </div>
        </div>
    </div>
    
    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
            crossorigin=""></script>
    
    <script>
        (function() {
            console.log('Starting map initialization...');
            
            // Wait for DOM to be fully loaded
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeMap);
            } else {
                initializeMap();
            }
            
            function initializeMap() {
                console.log('DOM ready, initializing map...');
                
                // Check if Leaflet is loaded
                if (typeof L === 'undefined') {
                    console.error('Leaflet library not loaded!');
                    return;
                }
                
                // Check if map container exists
                var mapContainer = document.getElementById('map');
                if (!mapContainer) {
                    console.error('Map container not found!');
                    return;
                }
                
                console.log('Map container found, creating map...');
                
                // Initialize map centered on Sumatra, Java, and Sulawesi
                var map = L.map('map', {
                    center: [-2.5, 110], // Centered on the three main islands
                    zoom: 6,
                    zoomControl: true,
                    scrollWheelZoom: true,
                    doubleClickZoom: true,
                    boxZoom: true,
                    keyboard: true,
                    dragging: true,
                    touchZoom: true,
                    minZoom: 3,
                    maxZoom: 12
                });
                
                console.log('Map created successfully');
                
                // Add tile layer with white/light theme
                var tileLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors © <a href="https://carto.com/attributions">CARTO</a>',
                    subdomains: 'abcd',
                    maxZoom: 12,
                    minZoom: 3
                });
                
                tileLayer.addTo(map);
                
                // Add event listeners for tile layer debugging
                tileLayer.on('loading', function() {
                    console.log('Tile layer loading...');
                });
                
                tileLayer.on('load', function() {
                    console.log('Tile layer loaded successfully');
                });
                
                tileLayer.on('tileerror', function(e) {
                    console.error('Tile loading error:', e);
                });
                
                console.log('Tile layer added with debugging');
                
                // Define NEW marker locations - properly distributed across Indonesia
                var markers = [
                    // PULAU SUMATERA (5 markers)
                    { lat: 3.5952, lng: 98.6722, color: '#e74c3c', title: 'Medan, Sumatera Utara', animation: 'marker-bounce' },
                    { lat: 0.5071, lng: 101.4478, color: '#3498db', title: 'Pekanbaru, Riau', animation: 'marker-pulse' },
                    { lat: -0.9553, lng: 100.3616, color: '#2ecc71', title: 'Padang, Sumatera Barat', animation: 'marker-shake' },
                    { lat: -2.9761, lng: 104.7754, color: '#f39c12', title: 'Palembang, Sumatera Selatan', animation: 'marker-bounce-delayed' },
                    { lat: 5.5483, lng: 95.3238, color: '#9b59b6', title: 'Banda Aceh, Aceh', animation: 'marker-pulse-delayed' },
                    
                    // PULAU SULAWESI (4 markers)
                    { lat: -5.1477, lng: 119.4327, color: '#4ecdc4', title: 'Makassar, Sulawesi Selatan', animation: 'marker-pulse-delayed' },
                    { lat: 1.4748, lng: 124.8421, color: '#45b7d1', title: 'Manado, Sulawesi Utara', animation: 'marker-shake-delayed' },
                    { lat: -3.9778, lng: 122.5194, color: '#96ceb4', title: 'Kendari, Sulawesi Tenggara', animation: 'marker-bounce' },
                    { lat: -0.8917, lng: 119.8707, color: '#feca57', title: 'Palu, Sulawesi Tengah', animation: 'marker-pulse' }
                ];
                
                console.log('Starting to add ' + markers.length + ' markers to map...');
                
                // Clear any existing markers first
                map.eachLayer(function(layer) {
                    if (layer instanceof L.Marker) {
                        map.removeLayer(layer);
                    }
                });
                
                // Add new markers with simplified approach
                var markerObjects = [];
                
                for (var i = 0; i < markers.length; i++) {
                    var marker = markers[i];
                    
                    // Validate marker coordinates
                    if (!marker.lat || !marker.lng || isNaN(marker.lat) || isNaN(marker.lng)) {
                        console.error('❌ Invalid coordinates for marker: ' + marker.title);
                        continue;
                    }
                    
                    // Validate Indonesia coordinate ranges
                    if (marker.lat < -11 || marker.lat > 6 || marker.lng < 95 || marker.lng > 141) {
                        console.warn('⚠️ Coordinates outside Indonesia range for: ' + marker.title);
                    }
                    
                    console.log('Adding marker ' + (i + 1) + '/' + markers.length + ': ' + marker.title);
                    console.log('Coordinates: [' + marker.lat + ', ' + marker.lng + ']');
                    
                    // Create animated marker icon with child element for animation
                    var customIcon = L.divIcon({
                        className: 'custom-marker ' + marker.animation,
                        html: '<div class="custom-marker-inner" style="background-color: ' + marker.color + '; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 3px 6px rgba(0,0,0,0.4);"></div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    });
                    
                    // Create and add marker to map
                    var leafletMarker = L.marker([marker.lat, marker.lng], { 
                        icon: customIcon,
                        title: marker.title
                    }).addTo(map);
                    
                    console.log('✓ Marker added with animation: ' + marker.animation + ' - ' + marker.title);
                    
                    // Add tooltip
                    leafletMarker.bindTooltip(marker.title, {
                        permanent: false,
                        direction: 'top',
                        offset: [0, -8]
                    });
                    
                    // Store for bounds calculation
                    markerObjects.push(leafletMarker);
                    
                    console.log('✓ Marker added successfully: ' + marker.title);
                }
                
                console.log('All ' + markerObjects.length + ' markers added to map');
                
                // Fit map to show all markers
                if (markerObjects.length > 0) {
                    console.log('Calculating bounds for ' + markerObjects.length + ' markers...');
                    
                    var group = L.featureGroup(markerObjects);
                    var bounds = group.getBounds();
                    
                    console.log('Bounds calculated:', bounds);
                    console.log('Southwest:', bounds.getSouthWest());
                    console.log('Northeast:', bounds.getNorthEast());
                    
                    // Validate bounds
                    var sw = bounds.getSouthWest();
                    var ne = bounds.getNorthEast();
                    
                    if (sw.lat && sw.lng && ne.lat && ne.lng) {
                        console.log('Bounds validation passed');
                        
                        // Set map view to show all markers with immediate effect
                        setTimeout(function() {
                            console.log('Setting map view with zoom 6...');
                            map.setView([-2.5, 110], 6, {
                                animate: true,
                                duration: 1.0
                            });
                            console.log('✓ Map view set successfully!');
                            console.log('Current map center:', map.getCenter());
                            console.log('Current map zoom:', map.getZoom());
                        }, 300); // Reduced delay for faster response
                    } else {
                        console.error('❌ Invalid bounds calculated');
                        // Fallback to manual center with zoom 6
                        map.setView([-2.5, 110], 6);
                    }
                } else {
                    console.error('❌ No markers found for bounds calculation');
                    // Fallback to manual center with zoom 6
                    map.setView([-2.5, 110], 6);
                }
            }
        })();
    </script>
</body>
</html>