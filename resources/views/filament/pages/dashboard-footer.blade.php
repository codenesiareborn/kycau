@push('scripts')
    {{-- Leaflet JavaScript --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

    <script>
    let customerMapInstance = null;
    let isMapFullscreen = false;

    function initCustomerMap() {
        // Wait for DOM to be ready
        if (!document.getElementById('customerMap')) {
            setTimeout(initCustomerMap, 100);
            return;
        }

        // Clear existing map if any
        if (customerMapInstance) {
            customerMapInstance.remove();
            customerMapInstance = null;
        }

        // Get the map data from the global variable set by the widget
        const customerMapData = window.customerMapData || [];

        // Initialize map centered on Indonesia
        customerMapInstance = L.map('customerMap', {
            scrollWheelZoom: true,
            dragging: true,
            zoomControl: true
        }).setView([-2.5, 118], 5);

        // Add white minimalist tile layer
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap contributors © CARTO',
            subdomains: 'abcd',
            maxZoom: 18
        }).addTo(customerMapInstance);

        // Add customer markers
        customerMapData.forEach(customer => {
            if (!customer.lat || !customer.lng) return;

            let markerColor, markerSize;

            // Determine color and size based on amount
            if (customer.amount < 1000000) {
                markerColor = 'marker-yellow';
                markerSize = 12;
            } else if (customer.amount <= 5000000) {
                markerColor = 'marker-orange';
                markerSize = 16;
            } else {
                markerColor = 'marker-green';
                markerSize = 20;
            }

            const customIcon = L.divIcon({
                className: `customer-marker ${markerColor}`,
                html: `<div style="width: ${markerSize}px; height: ${markerSize}px;" class="customer-marker ${markerColor}"></div>`,
                iconSize: [markerSize, markerSize],
                iconAnchor: [markerSize/2, markerSize/2]
            });

            const marker = L.marker([customer.lat, customer.lng], { icon: customIcon })
                .bindPopup(`
                    <div style="padding: 12px; min-width: 200px;">
                        <h4 style="margin: 0 0 8px 0; color: #134e4a; font-weight: 600;">${customer.name}</h4>
                        <div style="margin-bottom: 8px; font-size: 12px; color: #6b7280;">
                            <i class="fas fa-map-marker-alt"></i> ${customer.address || ''}, ${customer.city}
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px;">
                            <div>
                                <div style="color: #6b7280;">Produk:</div>
                                <div style="font-weight: 600; color: #1f2937;">${customer.products}</div>
                            </div>
                            <div>
                                <div style="color: #6b7280;">Total:</div>
                                <div style="font-weight: 600; color: #134e4a;">Rp ${customer.amount.toLocaleString('id-ID')}</div>
                            </div>
                        </div>
                    </div>
                `)
                .addTo(customerMapInstance);
        });

        // Add legend
        const legend = L.control({ position: 'bottomright' });
        legend.onAdd = function(map) {
            const div = L.DomUtil.create('div', 'info legend');
            div.innerHTML = `
                <div style="background: rgba(255, 255, 255, 0.95); padding: 16px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-size: 12px;">
                    <h4 style="margin: 0 0 12px 0; color: #134e4a; font-weight: 600;">Kategori Transaksi</h4>
                    <div style="display: flex; align-items: center; margin-bottom: 6px;">
                        <div style="width: 12px; height: 12px; background: #fbbf24; border-radius: 50%; margin-right: 8px;"></div>
                        <span>< Rp 1 Juta</span>
                    </div>
                    <div style="display: flex; align-items: center; margin-bottom: 6px;">
                        <div style="width: 16px; height: 16px; background: #f97316; border-radius: 50%; margin-right: 8px;"></div>
                        <span>Rp 1-5 Juta</span>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <div style="width: 20px; height: 20px; background: #10b981; border-radius: 50%; margin-right: 8px;"></div>
                        <span>> Rp 5 Juta</span>
                    </div>
                </div>
            `;
            return div;
        };
        legend.addTo(customerMapInstance);

        // Fix map tiles loading issue
        setTimeout(() => {
            customerMapInstance.invalidateSize();
        }, 250);
    }

    function toggleMapFullscreen() {
        const mapContainer = document.getElementById('mapContainer');
        const fullscreenBtn = document.getElementById('fullscreenBtn');

        if (!isMapFullscreen) {
            // Enter fullscreen
            mapContainer.classList.add('map-fullscreen');
            fullscreenBtn.innerHTML = `
                <svg class="fi-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6.25 7h-2.5a.75.75 0 0 1 0-1.5h2.5a.75.75 0 0 1 0 1.5ZM6.25 10h-2.5a.75.75 0 0 1 0-1.5h2.5a.75.75 0 0 1 0 1.5ZM16.25 7h-2.5a.75.75 0 0 1 0-1.5h2.5a.75.75 0 0 1 0 1.5ZM16.25 10h-2.5a.75.75 0 0 1 0-1.5h2.5a.75.75 0 0 1 0 1.5ZM6 17v-2.5a.75.75 0 0 0-1.5 0V17a.75.75 0 0 0 1.5 0ZM6 6.5V4a.75.75 0 0 0-1.5 0v2.5a.75.75 0 0 0 1.5 0ZM12.5 17v-2.5a.75.75 0 0 0-1.5 0V17a.75.75 0 0 0 1.5 0ZM12.5 6.5V4a.75.75 0 0 0-1.5 0v2.5a.75.75 0 0 0 1.5 0Z" />
                </svg>
                <span>Exit Fullscreen</span>
            `;

            isMapFullscreen = true;
        } else {
            // Exit fullscreen
            mapContainer.classList.remove('map-fullscreen');
            fullscreenBtn.innerHTML = `
                <svg class="fi-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.75 7h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1 0-1.5ZM13.75 10h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1 0-1.5ZM3.75 7h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1 0-1.5ZM3.75 10h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1 0-1.5ZM14 17v-2.5a.75.75 0 0 0-1.5 0V17a.75.75 0 0 0 1.5 0ZM14 6.5V4a.75.75 0 0 0-1.5 0v2.5a.75.75 0 0 0 1.5 0ZM7.5 17v-2.5a.75.75 0 0 0-1.5 0V17a.75.75 0 0 0 1.5 0ZM7.5 6.5V4a.75.75 0 0 0-1.5 0v2.5a.75.75 0 0 0 1.5 0Z" />
                </svg>
                <span>Fullscreen</span>
            `;

            isMapFullscreen = false;
        }

        // Reinitialize map after layout change
        setTimeout(() => {
            if (customerMapInstance) {
                customerMapInstance.invalidateSize();
            }
        }, 100);
    }

    // Expose initCustomerMap globally for Alpine.js
    window.initCustomerMap = initCustomerMap;

    // Reinitialize map when Livewire updates (filters change)
    document.addEventListener('livewire:navigated', () => {
        setTimeout(() => {
            if (document.getElementById('customerMap')) {
                initCustomerMap();
            }
        }, 300);
    });
    </script>
@endpush
