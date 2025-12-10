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
        body { font-family: 'Inter', sans-serif; min-height: 100vh; overflow-x: hidden; position: relative; }
        #map { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; }
        .logo { position: fixed; top: 20px; left: 20px; background: #2d5a3d; color: white; padding: 8px 16px; border-radius: 8px; font-size: 16px; font-weight: 700; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
        .register-container { position: relative; z-index: 100; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
        .register-card { background: rgba(255,255,255,.97); backdrop-filter: blur(10px); border-radius: 16px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,.15); border: 1px solid rgba(255,255,255,.2); max-width: 800px; width: 100%; }
        .register-header { text-align: center; margin-bottom: 24px; }
        .register-title { font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 6px; }
        .register-subtitle { font-size: 14px; color: #6b7280; font-weight: 400; }
        
        /* Package Selection */
        .packages-section { margin-bottom: 24px; }
        .packages-title { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 12px; }
        .packages-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; }
        .package-card { 
            border: 2px solid #e5e7eb; 
            border-radius: 12px; 
            padding: 16px; 
            cursor: pointer; 
            transition: all .2s ease;
            position: relative;
            background: white;
        }
        .package-card:hover:not(.disabled) { border-color: #2d5a3d; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(45,90,61,.15); }
        .package-card.selected { border-color: #2d5a3d; background: rgba(45,90,61,.05); }
        .package-card.disabled { opacity: 0.6; cursor: not-allowed; background: #f9fafb; }
        .package-card.disabled:hover { transform: none; box-shadow: none; }
        .package-badge { 
            position: absolute; 
            top: -8px; 
            right: 12px; 
            background: #2d5a3d; 
            color: white; 
            font-size: 10px; 
            font-weight: 600; 
            padding: 2px 8px; 
            border-radius: 10px; 
        }
        .package-badge.coming-soon { background: #6b7280; }
        .package-name { font-size: 16px; font-weight: 700; color: #1f2937; margin-bottom: 4px; }
        .package-price { font-size: 18px; font-weight: 700; color: #2d5a3d; margin-bottom: 8px; }
        .package-price .period { font-size: 12px; font-weight: 400; color: #6b7280; }
        .package-duration { font-size: 12px; color: #6b7280; margin-bottom: 8px; }
        .package-features { list-style: none; }
        .package-features li { font-size: 11px; color: #4b5563; padding: 2px 0; display: flex; align-items: center; gap: 6px; }
        .package-features li i { color: #2d5a3d; font-size: 10px; }
        .package-radio { display: none; }
        
        /* Form */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
        .form-group { margin-bottom: 0; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-label { display:block; font-size:12px; font-weight:500; color:#374151; margin-bottom:4px; }
        .form-input { width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; background:white; transition: all .2s ease; }
        .form-input:focus { outline:none; border-color:#2d5a3d; box-shadow:0 0 0 3px rgba(45,90,61,.1); }
        .checkbox-group { display:flex; align-items:center; gap:8px; margin-top:8px; }
        .checkbox-group input[type="checkbox"] { width:16px; height:16px; accent-color:#2d5a3d; }
        .checkbox-label { font-size:12px; color:#6b7280; }
        .register-button { width:100%; background:#2d5a3d; color:white; border:none; padding:12px 18px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; transition: all .2s ease; margin-top: 20px; }
        .register-button:hover { background:#1f3d2a; transform: translateY(-1px); box-shadow:0 4px 12px rgba(45,90,61,.3); }
        .error-message { background:#fee2e2; color:#b91c1c; padding:10px 14px; border-radius:8px; font-size:12px; margin-bottom:16px; }
        .signup-link { text-align:center; font-size:13px; color:#6b7280; margin-top: 20px; }
        .signup-link a { color:#2d5a3d; text-decoration:none; font-weight:600; }
        .signup-link a:hover { text-decoration:underline; }
    </style>
</head>
<body>
    <div id="map"></div>
    <div class="logo">KYCAU.ID</div>

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1 class="register-title">Buat Akun Baru</h1>
                <p class="register-subtitle">Pilih paket dan lengkapi data untuk mendaftar</p>
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

                <!-- Package Selection -->
                <div class="packages-section">
                    <div class="packages-title">Pilih Paket</div>
                    <div class="packages-grid">
                        @foreach($packages as $package)
                            <label class="package-card {{ $package->is_active ? '' : 'disabled' }} {{ old('package_id', $defaultPackage?->id) == $package->id ? 'selected' : '' }}" 
                                   data-package-id="{{ $package->id }}">
                                <input type="radio" 
                                       name="package_id" 
                                       value="{{ $package->id }}" 
                                       class="package-radio"
                                       {{ $package->is_active ? '' : 'disabled' }}
                                       {{ old('package_id', $defaultPackage?->id) == $package->id ? 'checked' : '' }}>
                                
                                @if($package->is_trial)
                                    <span class="package-badge">Rekomendasi</span>
                                @elseif(!$package->is_active)
                                    <span class="package-badge coming-soon">Segera</span>
                                @endif
                                
                                <div class="package-name">{{ $package->name }}</div>
                                <div class="package-price">
                                    {{ $package->formatted_price }}
                                    @if($package->price > 0)
                                        <span class="period">/ {{ $package->duration_days ? $package->duration_days . ' hari' : 'selamanya' }}</span>
                                    @endif
                                </div>
                                <div class="package-duration">
                                    <i class="fas fa-clock"></i> {{ $package->duration_label }}
                                </div>
                                @if($package->features)
                                    <ul class="package-features">
                                        @foreach(array_slice($package->features, 0, 3) as $feature)
                                            <li><i class="fas fa-check"></i> {{ $feature }}</li>
                                        @endforeach
                                        @if(count($package->features) > 3)
                                            <li><i class="fas fa-plus"></i> {{ count($package->features) - 3 }} fitur lainnya</li>
                                        @endif
                                    </ul>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" id="name" name="name" class="form-input" placeholder="Masukkan nama lengkap" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="contoh@email.com" value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Minimal 8 karakter" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Ulangi password" required>
                    </div>

                    <div class="form-group full-width">
                        <div class="checkbox-group">
                            <input type="checkbox" id="terms" name="terms" {{ old('terms') ? 'checked' : '' }} required>
                            <label for="terms" class="checkbox-label">Saya setuju dengan Syarat & Ketentuan yang berlaku</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="register-button">Daftar Sekarang</button>
            </form>

            <div class="signup-link">
                Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
            </div>
        </div>
    </div>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <script>
        const map = L.map('map').setView([-6.200000, 106.816666], 12);
        const tileLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap contributors © CARTO',
            subdomains: 'abcd',
            maxZoom: 19,
            minZoom: 3
        });
        tileLayer.addTo(map);

        const marker = L.marker([-6.200000, 106.816666]).addTo(map);
        marker.bindPopup('<b>KYCAU.ID</b><br>Pendaftaran Admin').openPopup();

        // Package selection
        document.querySelectorAll('.package-card:not(.disabled)').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.package-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });
    </script>
</body>
</html>