<x-filament::page>
    @php
        $session = $this->record;
        
        if (empty($session->route_polyline)) {
            app(\App\Services\DeliverySessionService::class)->calculateRouteGeometry($session);
            $session->refresh();
        }

        $stops = $session->sessionOrders()
            ->with('order')
            ->orderBy('stop_sequence')
            ->get();
        
        $stopsData = $stops->map(function ($so) {
            return [
                'sequence' => $so->stop_sequence,
                'eta' => $so->eta ?? '—',
                'order_number' => $so->order->order_number,
                'customer_name' => $so->order->customer_name ?? ($so->order->first_name . ' ' . $so->order->last_name),
                'address' => $so->order->address,
                'suburb' => $so->order->shipping_suburb ?? $so->order->shipping_city ?? $so->order->city ?? '—',
                'lat' => (float)$so->order->shipping_latitude,
                'lng' => (float)$so->order->shipping_longitude,
                'view_url' => \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $so->order_id]),
            ];
        })->toArray();
    @endphp

    <div class="delivery-session-detail-wrapper" style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Header Info / Stats Row -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <!-- Card 1: Orders -->
            <div class="stats-card" style="background: var(--card-bg, #fff); border-radius: 0.5rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid var(--border-color, #e5e7eb);">
                <div style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Total Orders</div>
                <div style="font-size: 1.875rem; font-weight: 700; color: var(--text-color, #111827); margin-top: 0.25rem;">{{ $stops->count() }}</div>
            </div>
            <!-- Card 2: Distance -->
            <div class="stats-card" style="background: var(--card-bg, #fff); border-radius: 0.5rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid var(--border-color, #e5e7eb);">
                <div style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Estimated Distance</div>
                <div style="font-size: 1.875rem; font-weight: 700; color: var(--text-color, #111827); margin-top: 0.25rem;">
                    {{ $session->estimated_distance_km ? number_format($session->estimated_distance_km, 1) . ' km' : '—' }}
                </div>
            </div>
            <!-- Card 3: Duration -->
            <div class="stats-card" style="background: var(--card-bg, #fff); border-radius: 0.5rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid var(--border-color, #e5e7eb);">
                <div style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Estimated Duration</div>
                <div style="font-size: 1.875rem; font-weight: 700; color: var(--text-color, #111827); margin-top: 0.25rem;">
                    @if ($session->estimated_duration_minutes)
                        @php
                            $hours = floor($session->estimated_duration_minutes / 60);
                            $mins = $session->estimated_duration_minutes % 60;
                        @endphp
                        {{ $hours > 0 ? "{$hours} hr " : "" }}{{ $mins }} min
                    @else
                        —
                    @endif
                </div>
            </div>
            <!-- Card 4: Generated At -->
            <div class="stats-card" style="background: var(--card-bg, #fff); border-radius: 0.5rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid var(--border-color, #e5e7eb);">
                <div style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Route Generated</div>
                <div style="font-size: 0.875rem; font-weight: 600; color: var(--text-color, #111827); margin-top: 0.75rem;">
                    {{ $session->route_generated_at ? $session->route_generated_at->format('d-M-Y h:i A') : '—' }}
                </div>
            </div>
        </div>

        <div class="grid-layout" style="display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; align-items: start;">
            
            <!-- Left Panel -->
            <div class="left-panel-card" style="background: var(--card-bg, #fff); border-radius: 0.5rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid var(--border-color, #e5e7eb); display: flex; flex-direction: column; gap: 1rem;">
                <h3 style="font-size: 1rem; font-weight: 600; border-bottom: 1px solid var(--border-color, #e5e7eb); padding-bottom: 0.5rem; margin: 0;">Session Details</h3>
                
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 600;">Session ID</span>
                    <div style="font-weight: 600; font-size: 1.1rem; color: var(--text-color, #111827);">#{{ $session->id }}</div>
                </div>

                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 600;">Assigned Staff</span>
                    <div style="font-weight: 500; color: var(--text-color, #111827);">{{ $session->staff->name ?? '—' }}</div>
                </div>

                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 600;">Warehouse (Origin)</span>
                    <div style="font-weight: 500; font-size: 0.875rem; color: var(--text-color, #111827);">
                        {{ config('delivery.store_address', 'Main Warehouse') }}
                    </div>
                </div>

                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 600;">Status</span>
                    <div style="margin-top: 0.25rem;">
                        <span style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; 
                            background: {{ $session->status === 'completed' ? '#d1fae5' : ($session->status === 'in_progress' ? '#dbeafe' : '#f3f4f6') }};
                            color: {{ $session->status === 'completed' ? '#065f46' : ($session->status === 'in_progress' ? '#1e40af' : '#374151') }};">
                            {{ ucfirst($session->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Panel (Map + Summary) -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Map Container -->
                <div style="background: var(--card-bg, #fff); border-radius: 0.5rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid var(--border-color, #e5e7eb);">
                    <div id="route-map" style="width: 100%; height: 450px; background: #e5e7eb;"></div>
                </div>

                <!-- Route Summary -->
                <div style="background: var(--card-bg, #fff); border-radius: 0.5rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid var(--border-color, #e5e7eb);">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-top: 0; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color, #e5e7eb); padding-bottom: 0.5rem;">Route Summary</h3>
                    
                    <div class="route-summary-timeline" style="display: flex; flex-direction: column; gap: 1rem; position: relative; padding-left: 1.5rem;">
                        <div style="position: absolute; left: 6px; top: 12px; bottom: 12px; width: 2px; background: #d1d5db;"></div>
                        
                        <!-- Warehouse -->
                        <div style="display: flex; gap: 1rem; align-items: start; position: relative;">
                            <div style="position: absolute; left: -19px; width: 10px; height: 10px; border-radius: 50%; background: #10b981; border: 2px solid #fff; box-shadow: 0 0 0 2px #10b981;"></div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem; color: #10b981;">Warehouse (Starting Point)</div>
                                <div style="font-size: 0.75rem; color: #6b7280;">{{ config('delivery.store_address', 'Main Warehouse') }}</div>
                            </div>
                        </div>

                        <!-- Stops -->
                        @foreach ($stopsData as $stop)
                            <div style="display: flex; gap: 1rem; align-items: start; position: relative;">
                                <div style="position: absolute; left: -19px; width: 10px; height: 10px; border-radius: 50%; background: #3b82f6; border: 2px solid #fff; box-shadow: 0 0 0 2px #3b82f6;"></div>
                                <div style="display: flex; justify-content: space-between; width: 100%; align-items: center; border-bottom: 1px solid var(--border-color, #f3f4f6); padding-bottom: 0.5rem;">
                                    <div>
                                        <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-color, #111827);">
                                            {{ $stop['sequence'] }}. Order #{{ $stop['order_number'] }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: #6b7280;">
                                            Customer: <span style="font-weight: 500;">{{ $stop['customer_name'] }}</span> | Suburb: <span style="font-weight: 500;">{{ $stop['suburb'] }}</span>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 0.813rem; font-weight: 600; color: #3b82f6;">ETA: {{ $stop['eta'] }}</div>
                                        <a href="{{ $stop['view_url'] }}" style="font-size: 0.75rem; color: #3b82f6; text-decoration: underline;">View Order</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Styles for Dark Mode / CSS variables -->
    <style>
        :root {
            --card-bg: #ffffff;
            --text-color: #111827;
            --border-color: #e5e7eb;
        }
        .dark {
            --card-bg: #161617;
            --text-color: #f3f4f6;
            --border-color: #374151;
        }
        .stats-card, .left-panel-card {
            background-color: var(--card-bg) !important;
            border-color: var(--border-color) !important;
        }
    </style>

    <script>
        window.routeMapData = {
            origin: {
                lat: {{ (float)config('delivery.store_coordinates.latitude', -37.8136) }},
                lng: {{ (float)config('delivery.store_coordinates.longitude', 144.9631) }},
                name: '{{ config('delivery.store_name', 'Main Warehouse') }}',
                address: '{{ config('delivery.store_address', '12 Main Street, Sydney NSW 2000') }}'
            },
            stops: @json($stopsData),
            encodedPolyline: @json($session->route_polyline)
        };

        // Create custom marker DOM element for AdvancedMarkerElement
        function createMarkerContent(type, number, status = 'pending') {
            if (type === 'warehouse') {
                const div = document.createElement('div');
                div.style.cssText = 'display:flex; align-items:center; justify-content:center; background:#10B981; color:#fff; font-weight:bold; font-size:12px; font-family:sans-serif; padding:5px 14px; border-radius:12px; border:2px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.3); white-space:nowrap;';
                div.textContent = 'Warehouse';
                return div;
            } else {
                let bgColor = '#3B82F6';
                if (status === 'completed') bgColor = '#10B981';
                else if (status === 'current') bgColor = '#F97316';

                const div = document.createElement('div');
                div.style.cssText = `display:flex; align-items:center; justify-content:center; width:32px; height:32px; background:${bgColor}; color:#fff; font-weight:bold; font-size:13px; font-family:sans-serif; border-radius:50%; border:3px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.3);`;
                div.textContent = number;
                return div;
            }
        }

        async function initMap() {
            const data = window.routeMapData;

            // Import AdvancedMarkerElement library
            const { Map, InfoWindow } = await google.maps.importLibrary('maps');
            let AdvancedMarkerElement;
            try {
                const markerLib = await google.maps.importLibrary('marker');
                AdvancedMarkerElement = markerLib.AdvancedMarkerElement;
            } catch (e) {
                console.warn('AdvancedMarkerElement not available, will use fallback markers.');
                AdvancedMarkerElement = null;
            }

            const map = new Map(document.getElementById('route-map'), {
                zoom: 12,
                center: data.origin,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                mapId: 'delivery_route_map'
            });

            const bounds = new google.maps.LatLngBounds();
            bounds.extend(data.origin);

            const infoWindow = new InfoWindow();

            // Warehouse Marker
            if (AdvancedMarkerElement) {
                const warehouseMarker = new AdvancedMarkerElement({
                    map: map,
                    position: data.origin,
                    title: 'Warehouse',
                    content: createMarkerContent('warehouse')
                });

                warehouseMarker.addListener('click', () => {
                    infoWindow.close();
                    infoWindow.setContent(`
                        <div style="padding: 0.5rem; font-family: sans-serif; min-width: 180px;">
                            <h4 style="margin: 0 0 0.25rem 0; color: #10b981; font-weight: 600; font-size: 1rem;">${data.origin.name}</h4>
                            <p style="margin: 0; font-size: 0.813rem; color: #4b5563;">${data.origin.address}</p>
                        </div>
                    `);
                    infoWindow.open(map, warehouseMarker);
                });
            } else {
                // Fallback to legacy Marker
                const warehouseMarker = new google.maps.Marker({
                    position: data.origin,
                    map: map,
                    title: 'Warehouse',
                    icon: { url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png' }
                });
                warehouseMarker.addListener('click', () => {
                    infoWindow.close();
                    infoWindow.setContent(`
                        <div style="padding: 0.5rem; font-family: sans-serif; min-width: 180px;">
                            <h4 style="margin: 0 0 0.25rem 0; color: #10b981; font-weight: 600; font-size: 1rem;">${data.origin.name}</h4>
                            <p style="margin: 0; font-size: 0.813rem; color: #4b5563;">${data.origin.address}</p>
                        </div>
                    `);
                    infoWindow.open(map, warehouseMarker);
                });
            }

            // Delivery Stop Markers
            data.stops.forEach((stop) => {
                const stopPos = { lat: stop.lat, lng: stop.lng };
                bounds.extend(stopPos);

                const clickHandler = (anchor) => {
                    infoWindow.close();
                    infoWindow.setContent(`
                        <div style="padding: 0.5rem; font-family: sans-serif; min-width: 180px;">
                            <div style="font-size: 0.75rem; font-weight: bold; color: #6b7280; text-transform: uppercase; margin-bottom: 0.25rem;">Stop #${stop.sequence}</div>
                            <h4 style="margin: 0 0 0.5rem 0; color: #1e40af; font-weight: 600; font-size: 1rem;">Order #${stop.order_number}</h4>
                            <p style="margin: 0 0 0.25rem 0; font-size: 0.813rem; color: #374151;"><strong>Customer:</strong> ${stop.customer_name}</p>
                            <p style="margin: 0 0 0.5rem 0; font-size: 0.813rem; color: #374151;"><strong>Address:</strong> ${stop.address}</p>
                            <a href="${stop.view_url}" style="font-size: 0.75rem; color: #2563eb; text-decoration: underline; font-weight: 500; display: inline-block;">View Order</a>
                        </div>
                    `);
                    infoWindow.open(map, anchor);
                };

                if (AdvancedMarkerElement) {
                    const marker = new AdvancedMarkerElement({
                        map: map,
                        position: stopPos,
                        title: `Stop #${stop.sequence}`,
                        content: createMarkerContent('delivery', stop.sequence, 'pending')
                    });
                    marker.addListener('click', () => clickHandler(marker));
                } else {
                    const marker = new google.maps.Marker({
                        position: stopPos,
                        map: map,
                        title: `Stop #${stop.sequence}`,
                        label: { text: String(stop.sequence), color: '#fff', fontWeight: 'bold' },
                        icon: { url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png' }
                    });
                    marker.addListener('click', () => clickHandler(marker));
                }
            });

            // Adjust zoom to fit bounds
            if (data.stops.length > 0) {
                map.fitBounds(bounds);
            }

            // Render stored route polyline (no browser-side routing API call)
            if (data.encodedPolyline) {
                try {
                    const geometryLib = await google.maps.importLibrary('geometry');
                    const decodedPath = google.maps.geometry.encoding.decodePath(data.encodedPolyline);

                    new google.maps.Polyline({
                        path: decodedPath,
                        geodesic: true,
                        strokeColor: '#3b82f6',
                        strokeOpacity: 0.8,
                        strokeWeight: 4,
                        map: map
                    });
                } catch (e) {
                    console.warn('Failed to decode stored polyline, falling back to straight lines:', e);
                    drawFallbackPolyline(map, data);
                }
            } else if (data.stops.length > 0) {
                // No stored polyline yet (legacy sessions), fall back to straight lines
                drawFallbackPolyline(map, data);
            }
        }

        function drawFallbackPolyline(map, data) {
            const routePath = [data.origin];
            data.stops.forEach(stop => {
                routePath.push({ lat: stop.lat, lng: stop.lng });
            });
            routePath.push(data.origin); // return trip to warehouse

            new google.maps.Polyline({
                path: routePath,
                geodesic: true,
                strokeColor: '#3b82f6',
                strokeOpacity: 0.6,
                strokeWeight: 3,
                strokePattern: [10, 5],
                map: map
            });
        }
    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=geometry&callback=initMap&v=weekly" async defer></script>

</x-filament::page>
