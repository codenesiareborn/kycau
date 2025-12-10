@php
    $latitude = $getLatitude();
    $longitude = $getLongitude();
@endphp

<!-- Debug: Log what values are being passed to Alpine -->
<script>
    console.log('=== MAP INITIALIZATION DEBUG ===');
    console.log('Raw latitude from getLatitude():', @json($latitude));
    console.log('Raw longitude from getLongitude():', @json($longitude));
    console.log('Customer record data:', @json($getRecord()));
</script>

<div x-data="customerMap({{ $latitude ? $latitude : 'null' }}, {{ $longitude ? $longitude : 'null' }})" x-init="setTimeout(() => init(), 500)" class="w-full" wire:ignore>
    <!-- Search Box Section - Inline Styles with Filament Design Tokens -->
    <div style="margin-bottom: 1rem;">
        <label style="display: block; font-size: 0.875rem; font-weight: 500; line-height: 1.25rem; color: rgb(17, 24, 39); margin-bottom: 0.5rem;">
            🔍 Cari Alamat
        </label>
        <div style="display: flex; gap: 0.5rem;">
            <input 
                type="text" 
                x-model="searchQuery"
                @keyup.enter="searchAddress()"
                @input.debounce.500ms="searchAddress()"
                placeholder="Masukkan alamat (contoh: Jalan Sudirman Jakarta)"
                style="flex: 1; border: 1px solid rgb(209, 213, 219); border-radius: 0.5rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; outline: none; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                onfocus="this.style.borderColor='rgb(59, 130, 246)'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                onblur="this.style.borderColor='rgb(209, 213, 219)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
            >
            <button 
                @click="searchAddress()"
                :disabled="searching"
                style="padding: 0.5rem 1rem; background-color: rgb(37, 99, 235); color: white; border: none; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; cursor: pointer; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                onmouseover="if(!this.disabled) this.style.backgroundColor='rgb(29, 78, 216)';"
                onmouseout="if(!this.disabled) this.style.backgroundColor='rgb(37, 99, 235)';"
                onfocus="this.style.outline='none'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                onblur="this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
            >
                <span x-show="!searching">Cari</span>
                <span x-show="searching">Mencari...</span>
            </button>
        </div>
        <div x-show="searchError" style="margin-top: 0.5rem; font-size: 0.875rem; color: rgb(220, 38, 38);" x-text="searchError"></div>
    </div>
    
    <!-- Map container with larger size -->
    <div id="customer-map-{{ $getId() }}" class="w-full rounded-lg border border-gray-300 bg-gray-100" style="height: 600px !important;"></div>
    
    <!-- Loading indicator -->
    <div x-show="!map" class="mt-2 text-sm text-gray-600">
        Loading map...
    </div>
    
    <!-- Current address display -->
    <div x-show="map" class="mt-2 text-sm text-gray-600">
        <div class="flex items-center space-x-2">
            <span class="font-medium">Alamat:</span>
        </div>
        <div x-text="currentAddress" class="mt-1 text-gray-700"></div>
        <div x-show="latitude && longitude" class="mt-1 text-xs text-gray-500">
            <span>Lat: </span><span x-text="latitude"></span>
            <span class="ml-2">Lng: </span><span x-text="longitude"></span>
        </div>
    </div>
</div>

<script>
// Ensure Leaflet is loaded before defining the component
if (typeof L === 'undefined') {
    // Load Leaflet if not available
    const leafletCSS = document.createElement('link');
    leafletCSS.rel = 'stylesheet';
    leafletCSS.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(leafletCSS);
    
    const leafletJS = document.createElement('script');
    leafletJS.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    document.head.appendChild(leafletJS);
}

function customerMap(initialLat, initialLng) {
    return {
        latitude: initialLat,
        longitude: initialLng,
        map: null,
        marker: null,
        currentAddress: 'Loading address...',
        searchQuery: '',
        searching: false,
        searchError: '',
        
        init() {
            // Wait for Leaflet to be available
            if (typeof L === 'undefined') {
                setTimeout(() => this.init(), 200);
                return;
            }
            
            // Prevent duplicate initialization
            if (this.map) {
                console.log('Map already initialized, skipping...');
                return;
            }
            
            console.log('Initializing map with:', initialLat, initialLng);
            
            try {
                // Check if container exists and is properly rendered
                const container = document.getElementById('customer-map-{{ $getId() }}');
                if (!container) {
                    console.error('Map container not found');
                    return;
                }
                
                // Check if container has dimensions
                if (container.offsetWidth === 0 || container.offsetHeight === 0) {
                    console.log('Container not ready, retrying...');
                    setTimeout(() => this.init(), 100);
                    return;
                }
                
                // Clear any existing map content
                if (this.map) {
                    this.map.remove();
                    this.map = null;
                }
                container.innerHTML = '';
                
                // Initialize map
                this.map = L.map('customer-map-{{ $getId() }}', {
                    center: [initialLat || -6.2088, initialLng || 106.8456],
                    zoom: 13
                });
                
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
                    this.updateFormFields();
                    this.reverseGeocode(position.lat, position.lng);
                });
                
                // Handle map click
                this.map.on('click', (e) => {
                    const position = e.latlng;
                    this.marker.setLatLng(position);
                    this.latitude = position.lat.toFixed(8);
                    this.longitude = position.lng.toFixed(8);
                    this.updateFormFields();
                    this.reverseGeocode(position.lat, position.lng);
                });
                
                // Initial geocoding if coordinates exist
                if (initialLat && initialLng) {
                    this.reverseGeocode(initialLat, initialLng);
                } else {
                    this.currentAddress = 'Set location by dragging marker or clicking on map';
                }
                
                console.log('Map initialized successfully');
                
            } catch (error) {
                console.error('Map initialization error:', error);
                // Reset state on error
                this.map = null;
                this.marker = null;
            }
        },
        
        searchAddress() {
            if (!this.searchQuery.trim()) {
                this.searchError = 'Masukkan alamat yang ingin dicari';
                return;
            }
            
            this.searching = true;
            this.searchError = '';
            
            // Use Nominatim search API
            const searchUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}&limit=1&addressdetails=1`;
            
            fetch(searchUrl)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        const lat = parseFloat(result.lat);
                        const lng = parseFloat(result.lon);
                        
                        console.log('Search result:', result);
                        
                        // Update coordinates
                        this.latitude = lat.toFixed(8);
                        this.longitude = lng.toFixed(8);
                        
                        // Update map center and marker
                        this.map.setView([lat, lng], 15);
                        this.marker.setLatLng([lat, lng]);
                        
                        // Update form fields
                        this.updateFormFields();
                        
                        // Trigger reverse geocoding for full address
                        this.reverseGeocode(lat, lng);
                        
                        console.log('Address found and updated');
                    } else {
                        this.searchError = 'Alamat tidak ditemukan. Coba dengan kata kunci yang lebih spesifik.';
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    this.searchError = 'Terjadi kesalahan saat mencari alamat. Silakan coba lagi.';
                })
                .finally(() => {
                    this.searching = false;
                });
        },
        
        updateFormFields() {
            // Use Livewire $wire.set() for proper form synchronization
            try {
                if (window.Livewire && this.$wire) {
                    // Update latitude and longitude via Livewire (nested in data array)
                    this.$wire.set('data.latitude', this.latitude);
                    this.$wire.set('data.longitude', this.longitude);
                    console.log('Updated Livewire fields:', {
                        latitude: this.latitude,
                        longitude: this.longitude
                    });
                    
                    // Debug: Verify Livewire state actually contains the values
                    setTimeout(() => {
                        const livewireData = this.$wire.get('data');
                        console.log('Current Livewire data state:', livewireData);
                        console.log('Latitude in Livewire:', livewireData?.latitude);
                        console.log('Longitude in Livewire:', livewireData?.longitude);
                    }, 100);
                } else {
                    console.error('Livewire or $wire not available');
                }
            } catch (error) {
                console.error('Error updating Livewire fields:', error);
            }
        },
        
        reverseGeocode(lat, lng) {
            // Update loading state
            this.currentAddress = 'Loading address...';
            
            // Debounced reverse geocoding
            clearTimeout(this.geocodeTimeout);
            this.geocodeTimeout = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.display_name) {
                            // Update address display
                            this.currentAddress = data.display_name;
                            
                            // Update address field via Livewire (nested in data array)
                            try {
                                if (window.Livewire && this.$wire) {
                                    this.$wire.set('data.address', data.display_name);
                                    console.log('Updated address via Livewire:', data.display_name);
                                } else {
                                    console.error('Livewire or $wire not available for address');
                                }
                            } catch (error) {
                                console.error('Error updating address via Livewire:', error);
                            }
                            
                            // Try to match city with laravolt indonesia
                            this.matchCity(data);
                        } else {
                            this.currentAddress = 'Address not found';
                        }
                    })
                    .catch(error => {
                        console.error('Geocoding error:', error);
                        this.currentAddress = 'Error loading address';
                    });
            }, 500);
        },
        
        matchCity(geodata) {
            console.log('=== CITY MATCHING DEBUG ===');
            console.log('Full geodata structure:', geodata);
            console.log('Address object:', geodata.address);
            
            // Try multiple address components for city name
            const possibleCityNames = [
                geodata.address?.city,
                geodata.address?.town,
                geodata.address?.village,
                geodata.address?.county,
                geodata.address?.state_district,
                geodata.address?.state,
                geodata.address?.province
            ].filter(Boolean);
            
            console.log('Possible city names found:', possibleCityNames);
            
            if (possibleCityNames.length > 0) {
                // Prioritize county (regency) for better matching
                let cityName = geodata.address?.county || possibleCityNames[0];
                
                console.log('Selected city name for API call:', cityName);
                console.log('Sending request to /api/cities/search?q=' + encodeURIComponent(cityName));
                
                // Set loading state
                this.searching = true;
                // Expand translation dictionary for more Indonesian cities
                const cityTranslations = {
                    'Central Jakarta': 'JAKARTA PUSAT',
                    'South Jakarta': 'JAKARTA SELATAN',
                    'North Jakarta': 'JAKARTA UTARA',
                    'East Jakarta': 'JAKARTA TIMUR',
                    'West Jakarta': 'JAKARTA BARAT',
                    'Jakarta': 'JAKARTA PUSAT',
                    'Gunungkidul Regency': 'GUNUNGKIDUL',
                    'Special Region of Yogyakarta': 'YOGYAKARTA',
                    'Yogyakarta': 'YOGYAKARTA',
                    'Sleman Regency': 'SLEMAN',
                    'Bantul Regency': 'BANTUL',
                    'Kulon Progo Regency': 'KULON PROGO'
                };
                
                // Simplified approach: use the prioritized city name
                const originalName = cityName;
                
                // Apply translation if available
                if (cityTranslations[cityName]) {
                    cityName = cityTranslations[cityName];
                    console.log('Translated city name:', cityName, 'from:', originalName);
                }
                
                // Simple search with timeout
                console.log(`Searching for city: ${cityName}`);
                
                // Create timeout promise
                const timeoutPromise = new Promise((_, reject) => {
                    setTimeout(() => reject(new Error('City search timeout')), 15000);
                });
                
                // Create search promise
                const searchPromise = fetch(`/api/cities/search?q=${encodeURIComponent(cityName)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.id) {
                            console.log('Found city:', data.name, '(ID:', data.id, ')');
                            
                            // Update city field via Livewire (nested in data array)
                            try {
                                if (window.Livewire && this.$wire) {
                                    this.$wire.set('data.city_id', data.id);
                                    console.log('Updated city via Livewire:', data.name, '(ID:', data.id, ')');
                                } else {
                                    console.error('Livewire or $wire not available for city');
                                }
                            } catch (error) {
                                console.error('Error updating city via Livewire:', error);
                            }
                            return true;
                        } else {
                            // Try with KOTA prefix
                            return fetch(`/api/cities/search?q=${encodeURIComponent('KOTA ' + cityName)}`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data && data.id) {
                                        console.log('Found city with KOTA prefix:', data.name, '(ID:', data.id, ')');
                                        
                                        try {
                                            if (window.Livewire && this.$wire) {
                                                this.$wire.set('data.city_id', data.id);
                                                console.log('Updated city via Livewire:', data.name, '(ID:', data.id, ')');
                                            }
                                        } catch (error) {
                                            console.error('Error updating city via Livewire:', error);
                                        }
                                        return true;
                                    }
                                    return false;
                                });
                        }
                    });
                
                // Race between search and timeout
                Promise.race([searchPromise, timeoutPromise])
                    .then(success => {
                        if (!success) {
                            console.log('City not found in database');
                        }
                    })
                    .catch(error => {
                        console.error('City matching failed:', error.message);
                    })
                    .finally(() => {
                        // Clear loading state
                        this.searching = false;
                        console.log('=== CITY MATCHING COMPLETED ===');
                    });
                    
            } else {
                console.log('No city name found in geodata address');
            }
        }
    }
}
</script>
