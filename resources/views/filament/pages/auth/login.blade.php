<x-filament-panels::page.simple>
    <!-- Custom Styles -->
    <style>
        body {
            position: relative;
            overflow: hidden;
        }

        /* Map Container */
        #map {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* Overlay */
        .map-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);
            z-index: 2;
        }

        /* Logo */
        .logo {
            position: fixed;
            top: 30px;
            left: 30px;
            z-index: 10;
            color: white;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            letter-spacing: 1px;
        }

        /* Filament login card styling */
        .fi-simple-main {
            position: relative;
            z-index: 5;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px);
            border-radius: 20px !important;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15) !important;
        }

        /* Floating elements */
        .floating-element {
            position: fixed;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
            z-index: 3;
        }

        .floating-element:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .logo {
                top: 20px;
                left: 20px;
                font-size: 24px;
            }
        }
    </style>

    <!-- Map Container -->
    <div id="map"></div>
    
    <!-- Overlay -->
    <div class="map-overlay"></div>
    
    <!-- Logo -->
    <div class="logo">KYCAU.ID</div>
    
    <!-- Floating Elements -->
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
          crossorigin=""/>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
            crossorigin=""></script>

    <!-- Map Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize map
            var map = L.map('map', {
                zoomControl: false,
                attributionControl: false,
                dragging: false,
                touchZoom: false,
                doubleClickZoom: false,
                scrollWheelZoom: false,
                boxZoom: false,
                keyboard: false
            }).setView([-6.2088, 106.8456], 11);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: false
            }).addTo(map);

            // Smooth zoom animation
            setTimeout(function() {
                map.setView([-6.2088, 106.8456], 12, {
                    animate: true,
                    duration: 2
                });
            }, 1000);
        });
    </script>
</x-filament-panels::page.simple>