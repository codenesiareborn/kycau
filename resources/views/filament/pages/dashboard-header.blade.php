@push('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />

    <style>
        /* Custom Leaflet Styles */
        .leaflet-container {
            background: #ffffff;
        }

        .customer-marker {
            border-radius: 50% !important;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            display: flex !important;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 10px;
            font-weight: bold;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            aspect-ratio: 1 / 1 !important;
            overflow: hidden;
        }

        /* Ensure Leaflet's divIcon wrapper is also circular */
        .leaflet-marker-icon.customer-marker {
            border-radius: 50% !important;
        }

        .marker-yellow {
            background: #fbbf24;
        }

        .marker-orange {
            background: #f97316;
        }

        .marker-green {
            background: #10b981;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 8px;
        }

        .leaflet-popup-content {
            margin: 0;
        }

        .map-fullscreen {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 9999 !important;
            background: white !important;
        }

        .map-fullscreen #customerMap {
            height: calc(100vh - 80px) !important;
            border-radius: 0 !important;
        }

        .fullscreen-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
@endpush
