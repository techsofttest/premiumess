// delivery-map.js - Leaflet map logic for delivery dashboard

document.addEventListener('livewire:initialized', () => {
    console.log('Livewire initialized, setting up map logic.');

    const mapElement = document.getElementById('delivery-map');
    let map = null;
    let markersGroup = null;
    let routeLine = null;

    function initMap() {
        if (map || !mapElement) {
            return;
        }

        // Default to Melbourne
        const initialLat = -37.8136;
        const initialLng = 144.9631;

        map = L.map(mapElement).setView([initialLat, initialLng], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        markersGroup = L.layerGroup().addTo(map);
        console.log('Map initialized.');
    }

    function updateMap(data) {
        console.log('Received map data:', data);

        if (!map) {
            initMap();
        }
        
        if (!map) {
            console.error('Map could not be initialized.');
            return;
        }

        const store = data.store;
        const stops = data.stops;

        // Clear existing layers
        markersGroup.clearLayers();
        if (routeLine) {
            map.removeLayer(routeLine);
            routeLine = null;
        }

        const coordinates = [];

        // Store Marker
        if (store) {
            const storeIcon = L.divIcon({
                html: `<div style="background-color: #ef4444; width: 16px; height: 16px; border-radius: 50%; border: 3px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                className: 'custom-store-icon',
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            L.marker([store.lat, store.lng], { icon: storeIcon })
                .addTo(markersGroup)
                .bindPopup('<strong>Warehouse / Store</strong><br>Start & End Point');
            coordinates.push([store.lat, store.lng]);
        }

        // Stops Markers
        if (stops && stops.length > 0) {
            stops.forEach(stop => {
                let color = '#3b82f6'; // blue (pending)
                if (stop.status === 'delivered') {
                    color = '#22c55e'; // green
                } else if (stop.status === 'failed') {
                    color = '#ef4444'; // red
                }

                const stopIcon = L.divIcon({
                    html: `<div style="background-color: ${color}; width: 24px; height: 24px; border-radius: 50%; border: 2px solid #ffffff; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 11px; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${stop.sequence}</div>`,
                    className: 'custom-stop-icon',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                L.marker([stop.lat, stop.lng], { icon: stopIcon })
                    .addTo(markersGroup)
                    .bindPopup(`<strong>Stop ${stop.sequence}</strong><br>Order: ${stop.order_number}<br>Customer: ${stop.customer}<br>ETA: ${stop.eta || 'N/A'}<br>Status: ${stop.status.toUpperCase()}`);
                coordinates.push([stop.lat, stop.lng]);
            });
        }
        
        // Polyline for optimized route
        if (coordinates.length > 1) {
            // Closed loop
            if(store) {
                coordinates.push([store.lat, store.lng]);
            }
            
            routeLine = L.polyline(coordinates, {
                color: '#6366f1',
                weight: 4,
                opacity: 0.7,
                dashArray: '5, 8'
            }).addTo(map);

            map.fitBounds(routeLine.getBounds(), { padding: [50, 50] });
        } else if (store) {
            map.setView([store.lat, store.lng], 15);
        }
    }
    
    // Initial setup
    initMap();

    // Listen for events from Livewire
    document.addEventListener('map-update', (event) => {
        updateMap(event.detail[0]);
    });

});
