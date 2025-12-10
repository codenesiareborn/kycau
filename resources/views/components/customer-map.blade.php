@php
    $record = $this->getRecord();
    $latitude = $record?->latitude;
    $longitude = $record?->longitude;
@endphp

@props([
    'name' => 'map'
])

<div x-data="customerMap({{ $latitude ? $latitude : 'null' }}, {{ $longitude ? $longitude : 'null' }})" class="w-full">
    <!-- Hidden inputs for coordinates -->
    <input type="hidden" name="latitude" x-model="latitude" />
    <input type="hidden" name="longitude" x-model="longitude" />
    
    <!-- Map container -->
    <div id="{{ $name }}-map" class="h-96 w-full rounded-lg border border-gray-300"></div>
    
    <!-- Current coordinates display -->
    <div class="mt-2 text-sm text-gray-600">
        <span>Latitude: </span><span x-text="latitude || 'Not set'"></span>
        <span class="ml-4">Longitude: </span><span x-text="longitude || 'Not set'"></span>
    </div>
</div>

<script>
function customerMap(initialLat, initialLng) {
    return {
        latitude: initialLat,
        longitude: initialLng,
        map: null,
        marker: null,
        
        init() {
            // Initialize map
            this.map = L.map('{{ $name }}-map').setView([
                initialLat || -6.2088, 
                initialLng || 106.8456
            ], 13);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);
            
            // Add marker
            this.marker = L.marker([
                initialLat || -6.2088, 
                initialLng || 106.8456
            ], {
                draggable: true
            }).addTo(this.map);
            
            // Handle marker drag
            this.marker.on('dragend', (e) => {
                const position = e.target.getLatLng();
                this.latitude = position.lat.toFixed(8);
                this.longitude = position.lng.toFixed(8);
                this.reverseGeocode(position.lat, position.lng);
            });
            
            // Handle map click
            this.map.on('click', (e) => {
                const position = e.latlng;
                this.marker.setLatLng(position);
                this.latitude = position.lat.toFixed(8);
                this.longitude = position.lng.toFixed(8);
                this.reverseGeocode(position.lat, position.lng);
            });
        },
        
        reverseGeocode(lat, lng) {
            // Debounced reverse geocoding
            clearTimeout(this.geocodeTimeout);
            this.geocodeTimeout = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.display_name) {
                            // Update address field
                            const addressField = document.querySelector('input[name="address"]');
                            if (addressField) {
                                addressField.value = data.display_name;
                                addressField.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            
                            // Try to match city with laravolt indonesia
                            this.matchCity(data);
                        }
                    })
                    .catch(error => console.error('Geocoding error:', error));
            }, 500);
        },
        
        matchCity(geodata) {
            if (geodata.address && geodata.address.city) {
                const cityName = geodata.address.city;
                
                // Find matching city in laravolt indonesia data
                fetch(`/api/cities/search?q=${encodeURIComponent(cityName)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.id) {
                            const cityField = document.querySelector('select[name="city_id"]');
                            if (cityField) {
                                cityField.value = data.id;
                                cityField.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                    })
                    .catch(error => console.error('City matching error:', error));
            }
        }
    }
}
</script>

<!-- Load Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
