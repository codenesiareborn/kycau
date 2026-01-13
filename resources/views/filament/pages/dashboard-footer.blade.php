@push('scripts')
    {{-- Leaflet JavaScript --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    
    {{-- Leaflet MarkerCluster Plugin --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js"></script>

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
            maxZoom: 22
        }).addTo(customerMapInstance);

        // Create marker cluster group
        const markerCluster = L.markerClusterGroup({
            // Custom cluster styling
            iconCreateFunction: function(cluster) {
                const count = cluster.getChildCount();
                let size, color;
                
                // Determine cluster size and color
                if (count < 5) {
                    size = 30;
                    color = '#fbbf24'; // Yellow
                } else if (count < 10) {
                    size = 40;
                    color = '#f97316'; // Orange
                } else {
                    size = 50;
                    color = '#10b981'; // Green
                }
                
                return L.divIcon({
                    html: `<div style="
                        background: ${color};
                        border-radius: 50%;
                        width: ${size}px;
                        height: ${size}px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        font-weight: bold;
                        font-size: ${size/3}px;
                        border: 3px solid white;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                    ">${count}</div>`,
                    className: 'custom-cluster-icon',
                    iconSize: [size, size],
                    iconAnchor: [size/2, size/2]
                });
            },
            // Enable spiderfy for "explode" effect
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            maxClusterRadius: 50, // Cluster markers within 50px
            spiderfyDistanceMultiplier: 2, // Spread markers further apart when spiderfied
            disableClusteringAtZoom: 18 // Stop clustering at street level
        });

        // Add customer markers to cluster
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
                html: '',
                iconSize: [markerSize, markerSize],
                iconAnchor: [markerSize/2, markerSize/2]
            });

            const marker = L.marker([customer.lat, customer.lng], { icon: customIcon })
                .bindPopup(`
                    <div style="padding: 12px; min-width: 300px; max-width: 400px;">
                        <h4 style="margin: 0 0 8px 0; color: #134e4a; font-weight: 600;">${customer.name}</h4>
                        <div style="margin-bottom: 8px; font-size: 12px; color: #6b7280;">
                            <i class="fas fa-map-marker-alt"></i> ${customer.address || ''}, ${customer.city}
                        </div>
                        <div style="margin-bottom: 12px; font-size: 12px;">
                            <span style="color: #6b7280;">Total Transaksi:</span>
                            <span style="font-weight: 600; color: #1f2937;">${customer.sales_count}x</span>
                            <span style="margin-left: 10px; color: #6b7280;">Total Amount:</span>
                            <span style="font-weight: 600; color: #134e4a;">Rp ${customer.amount.toLocaleString('id-ID')}</span>
                        </div>
                        <div style="border-top: 1px solid #e5e7eb; padding-top: 8px;">
                            <h5 style="margin: 0 0 8px 0; font-size: 13px; color: #374151; font-weight: 600;">Detail Transaksi:</h5>
                            <div style="max-height: 200px; overflow-y: auto;">
                                ${customer.sales_records.map(sale => `
                                    <div style="margin-bottom: 8px; padding: 8px; background: #f9fafb; border-radius: 6px; font-size: 11px;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                            <span style="font-weight: 600; color: #1f2937;">#${sale.sale_id}</span>
                                            <span style="color: #134e4a; font-weight: 600;">Rp ${sale.amount.toLocaleString('id-ID')}</span>
                                        </div>
                                        <div style="color: #6b7280; margin-bottom: 4px;">
                                            <i class="fas fa-calendar"></i> ${new Date(sale.date).toLocaleDateString('id-ID')}
                                        </div>
                                        <div style="color: #4b5563;">
                                            <i class="fas fa-box"></i> ${sale.products}
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                `);
            
            // Zoom and center on click, then open popup after animation
            marker.on('click', (e) => {
                const latlng = e.latlng;
                // customerMapInstance.flyTo(latlng, 16, { duration: 0.5 });
                setTimeout(() => {
                    marker.openPopup();
                }, 500);
            });

            // Add marker to cluster instead of directly to map
            markerCluster.addLayer(marker);
        });

        // Add marker cluster to map
        customerMapInstance.addLayer(markerCluster);

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
        const fullscreenOverlayBtn = document.getElementById('fullscreenOverlayBtn');

        if (!isMapFullscreen) {
            // Enter fullscreen
            mapContainer.classList.add('map-fullscreen');
            fullscreenBtn.innerHTML = `
                <svg class="fi-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6.25 7h-2.5a.75.75 0 0 1 0-1.5h2.5a.75.75 0 0 1 0 1.5ZM6.25 10h-2.5a.75.75 0 0 1 0-1.5h2.5a.75.75 0 0 1 0 1.5ZM16.25 7h-2.5a.75.75 0 0 1 0-1.5h2.5a.75.75 0 0 1 0 1.5ZM16.25 10h-2.5a.75.75 0 0 1 0-1.5h2.5a.75.75 0 0 1 0 1.5ZM6 17v-2.5a.75.75 0 0 0-1.5 0V17a.75.75 0 0 0 1.5 0ZM6 6.5V4a.75.75 0 0 0-1.5 0v2.5a.75.75 0 0 0 1.5 0ZM12.5 17v-2.5a.75.75 0 0 0-1.5 0V17a.75.75 0 0 0 1.5 0ZM12.5 6.5V4a.75.75 0 0 0-1.5 0v2.5a.75.75 0 0 0 1.5 0Z" />
                </svg>
                <span>Exit Fullscreen</span>
            `;

            // Show overlay button in fullscreen
            if (fullscreenOverlayBtn) {
                fullscreenOverlayBtn.style.display = 'inline-flex';
            }

            isMapFullscreen = true;

            // Add ESC key listener
            const escKeyListener = (e) => {
                if (e.key === 'Escape') {
                    toggleMapFullscreen();
                    document.removeEventListener('keydown', escKeyListener);
                }
            };
            document.addEventListener('keydown', escKeyListener);

            // Add click outside to close (on the background)
            const clickOutsideListener = (e) => {
                if (e.target === mapContainer) {
                    toggleMapFullscreen();
                    document.removeEventListener('click', clickOutsideListener);
                    document.removeEventListener('keydown', escKeyListener);
                }
            };
            setTimeout(() => {
                document.addEventListener('click', clickOutsideListener);
            }, 100);

        } else {
            // Exit fullscreen
            mapContainer.classList.remove('map-fullscreen');
            fullscreenBtn.innerHTML = `
                <svg class="fi-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.75 7h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1 0-1.5ZM13.75 10h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1 0-1.5ZM3.75 7h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1 0-1.5ZM3.75 10h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1 0-1.5ZM14 17v-2.5a.75.75 0 0 0-1.5 0V17a.75.75 0 0 0 1.5 0ZM14 6.5V4a.75.75 0 0 0-1.5 0v2.5a.75.75 0 0 0 1.5 0ZM7.5 17v-2.5a.75.75 0 0 0-1.5 0V17a.75.75 0 0 0 1.5 0ZM7.5 6.5V4a.75.75 0 0 0-1.5 0v2.5a.75.75 0 0 0 1.5 0Z" />
                </svg>
                <span>Fullscreen</span>
            `;

            // Hide overlay button when exiting fullscreen
            if (fullscreenOverlayBtn) {
                fullscreenOverlayBtn.style.display = 'none';
            }

            isMapFullscreen = false;

            // Clean up event listeners
            document.removeEventListener('keydown', (e) => {
                if (e.key === 'Escape') toggleMapFullscreen();
            });
            document.removeEventListener('click', (e) => {
                if (e.target === mapContainer) toggleMapFullscreen();
            });
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
