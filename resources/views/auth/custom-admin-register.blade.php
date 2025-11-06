<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KYCAU.ID - Admin Register</title>

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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; height: 100vh; overflow: hidden; position: relative; }
        #map { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; }
        .logo { position: absolute; top: 20px; left: 20px; background: #2d5a3d; color: white; padding: 8px 16px; border-radius: 8px; font-size: 16px; font-weight: 700; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
        .login-container { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; display: flex; align-items: center; justify-content: center; z-index: 1000; pointer-events: none; }
        .login-card { background: rgba(255,255,255,.95); backdrop-filter: blur(10px); border-radius: 16px; padding: 25px 22px; box-shadow: 0 20px 40px rgba(0,0,0,.1); border: 1px solid rgba(255,255,255,.2); animation: slideUp .6s ease-out, floatingCard 4s ease-in-out infinite 1s; max-width: 308px; width: 90%; pointer-events: auto; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity:1; transform: translateY(0);} }
        @keyframes floatingCard { 0%,100% { transform: translateY(0);} 50% { transform: translateY(-6px);} }
        .login-header { text-align: center; margin-bottom: 20px; }
        .login-title { font-size: 20px; font-weight: 700; color: #1f2937; margin-bottom: 4px; }
        .login-subtitle { font-size: 12px; color: #6b7280; font-weight: 400; }
        .form-group { margin-bottom: 14px; }
        .form-label { display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:3px; }
        .form-input { width:100%; padding:9px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; background:white; transition: all .2s ease; }
        .form-input:focus { outline:none; border-color:#2d5a3d; box-shadow:0 0 0 3px rgba(45,90,61,.1); }
        .checkbox-group { display:flex; align-items:center; gap:6px; margin-top:6px; }
        .checkbox-group input[type="checkbox"] { width:14px; height:14px; accent-color:#2d5a3d; }
        .checkbox-label { font-size:12px; color:#6b7280; }
        .login-button { width:100%; background:#2d5a3d; color:white; border:none; padding:11px 18px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; transition: all .2s ease; margin-top: 4px; margin-bottom: 18px; }
        .login-button:hover { background:#1f3d2a; transform: translateY(-1px); box-shadow:0 4px 12px rgba(45,90,61,.3); }
        .divider { text-align:center; margin:16px 0; position:relative; color:#9ca3af; }
        .error-message { background:#fee2e2; color:#b91c1c; padding:8px 12px; border-radius:8px; font-size:12px; margin-bottom:12px; }
        .signup-link { text-align:center; font-size:12px; color:#6b7280; }
        .signup-link a { color:#2d5a3d; text-decoration:none; font-weight:600; }
        .signup-link a:hover { text-decoration:underline; }
    </style>
</head>
<body>
    <div id="map"></div>
    <div class="logo">KYCAU.ID</div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">Create Account</h1>
                <p class="login-subtitle">Register to access the admin panel</p>
            </div>

            @if (session('status'))
                <div class="error-message">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('custom.register') }}">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" id="name" name="name" class="form-input" placeholder="Enter your full name" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Enter a password" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Confirm your password" required>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="terms" name="terms" {{ old('terms') ? 'checked' : '' }} required>
                    <label for="terms" class="checkbox-label">I agree to the Terms & Conditions</label>
                </div>

                <button type="submit" class="login-button">Create Account</button>
            </form>

            <div class="signup-link">
                Already have an account? <a href="{{ route('login') }}">Log in</a>
            </div>
        </div>
    </div>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <script>
        const map = L.map('map').setView([-6.200000, 106.816666], 12);
        // Use light/white themed basemap for cleaner look
        const tileLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap contributors © CARTO',
            subdomains: 'abcd',
            maxZoom: 19,
            minZoom: 3
        });
        tileLayer.addTo(map);

        const marker = L.marker([-6.200000, 106.816666]).addTo(map);
        marker.bindPopup('<b>KYCAU.ID</b><br>Admin Registration').openPopup();
    </script>
</body>
</html>