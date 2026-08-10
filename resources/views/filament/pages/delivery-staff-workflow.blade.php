<x-filament-panels::page>
    <div id="delivery-staff-workflow-root">
        <div class="app-container">
            @if (!$activeSession)
                <!-- No Active Session Screen -->
                <div class="card card-center">
                    <div class="icon-circle icon-circle-blue">
                        <svg width="32" height="32" style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </div>
                    <h2 class="card-title">No Active Delivery Session</h2>
                    <p class="card-subtitle">You do not have an active delivery session right now. Please set a delivery session to 'In Progress' in the admin panel to begin.</p>
                </div>
            @elseif (!$currentStop)
                <!-- Active Session, All Stops Completed Screen -->
                <div class="card card-center">
                    <div class="icon-circle icon-circle-success">
                        <svg width="32" height="32" style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h2 class="card-title">All Stops Completed!</h2>
                    <p class="card-subtitle">You have completed all scheduled deliveries for this session. Complete the session to finalize your shift logs.</p>

                    <button wire:click="completeSession" class="btn-success-full">
                        Complete Session & Log Off
                    </button>
                </div>
            @else
                <!-- Active Delivery Stop Detail Card -->
                <div class="stack-4">
                    <!-- Header / Progress bar -->
                    <div class="stop-header">
                        <span class="stop-number">
                            Stop #{{ $currentStop->stop_sequence }}
                        </span>
                        <span class="stop-eta-label">
                            ETA: <span class="stop-eta-value">{{ $currentStop->eta ?? 'N/A' }}</span>
                        </span>
                    </div>

                    <!-- Customer info card -->
                    <div class="detail-card stack-4">
                        <div>
                            <div class="field-label">Customer</div>
                            <div class="customer-name">{{ $currentStop->order?->customer_name }}</div>
                            <a href="tel:{{ $currentStop->order?->customer_phone }}" class="phone-link">
                                <svg class="icon-xs" width="16" height="16" style="width: 16px; height: 16px; display: inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $currentStop->order?->customer_phone }}
                            </a>
                        </div>

                        <hr class="divider"/>

                        <div>
                            <div class="field-label">Delivery Address</div>
                            <div class="address-text">
                                {{ $currentStop->order?->shipping_address_line_1 }}<br>
                                @if ($currentStop->order?->shipping_address_line_2)
                                    {{ $currentStop->order?->shipping_address_line_2 }}<br>
                                @endif
                                {{ $currentStop->order?->shipping_suburb }}, {{ $currentStop->order?->shipping_city }}
                            </div>

                            <!-- Navigation Link -->
                            @php
                                $lat = $currentStop->order?->shipping_latitude;
                                $lng = $currentStop->order?->shipping_longitude;
                                $placeId = $currentStop->order?->shipping_google_place_id;
                                $navUrl = $placeId 
                                    ? "https://www.google.com/maps/search/?api=1&query=Google&query_place_id={$placeId}"
                                    : "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";
                            @endphp
                            <a href="{{ $navUrl }}" target="_blank" class="btn-navigate">
                                <svg class="icon-md" width="20" height="20" style="width: 20px; height: 20px; display: inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Navigate with Google Maps
                            </a>
                        </div>

                        <hr class="divider"/>

                        <!-- Items List -->
                        <div>
                            <div class="field-label mb-2">Order Items</div>
                            <ul class="stack-2">
                                @foreach ($currentStop->order?->items ?? [] as $item)
                                    <li class="item-row">
                                        <span class="item-name">{{ $item->product_name ?? 'Product' }}</span>
                                        <span class="item-qty-badge">
                                            Qty: {{ $item->quantity }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        @if ($currentStop->order?->delivery_notes)
                            <hr class="divider"/>
                            <div>
                                <div class="field-label">Delivery Notes</div>
                                <p class="notes-text">
                                    "{{ $currentStop->order?->delivery_notes }}"
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Primary Action Buttons -->
                    <div class="action-grid">
                        <button wire:click="openFailureModal" class="btn-fail">
                            Mark Failed
                        </button>
                        
                        <button wire:click="openDeliveryModal" class="btn-deliver">
                            Mark Delivered
                        </button>
                    </div>
                </div>
            @endif

            <!-- Mark Delivered Modal Form -->
            @if ($showDeliveryModal)
                <div class="modal-overlay">
                    <div class="modal-box stack-4">
                        <h3 class="modal-title">Confirm Delivery</h3>

                        <!-- Compliance Section -->
                        <div class="compliance-box stack-3">
                            <label class="checkbox-label">
                                <input type="checkbox" wire:model.live="tempComplianceEnabled" class="checkbox-input">
                                <span class="checkbox-text">Enable Temp Compliance</span>
                            </label>

                            @if ($tempComplianceEnabled)
                                <div class="stack-3 pt-2">
                                    <div>
                                        <label class="field-label-form">Temperature Reading (°C)</label>
                                        <input type="number" step="0.1" wire:model="temperatureReading" class="form-input">
                                    </div>

                                    <div>
                                        <label class="field-label-form">Thermometer Photo</label>
                                        <input type="file" accept="image/*" capture="environment" wire:model="thermometerPhoto" class="file-input">
                                        <div wire:loading wire:target="thermometerPhoto" class="upload-status">Uploading image...</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="field-label-form">Delivery Notes (Optional)</label>
                            <textarea wire:model="deliveryNotes" rows="2" class="form-input"></textarea>
                        </div>

                        <div class="modal-actions">
                            <button type="button" wire:click="$set('showDeliveryModal', false)" class="btn-cancel">
                                Cancel
                            </button>
                            
                            <button type="button" onclick="getLocationAndSubmit()" class="btn-submit">
                                Submit Delivery
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Mark Failed Modal Form -->
            @if ($showFailureModal)
                <div class="modal-overlay">
                    <div class="modal-box stack-4">
                        <h3 class="modal-title">Log Delivery Failure</h3>

                        <div>
                            <label class="field-label-form">Reason for Failure</label>
                            <select wire:model="failureReason" class="form-input">
                                <option value="">-- Select Reason --</option>
                                <option value="Customer not home">Customer not home</option>
                                <option value="Incorrect address details">Incorrect address details</option>
                                <option value="Refused delivery">Refused delivery</option>
                                <option value="Safety/Access issue">Safety/Access issue</option>
                                <option value="Other / Damaged parcel">Other / Damaged parcel</option>
                            </select>
                            @error('failureReason') <span class="error-text">{{ $message }}</span> @enderror
                        </div>

                        <div class="modal-actions">
                            <button type="button" wire:click="$set('showFailureModal', false)" class="btn-cancel">
                                Cancel
                            </button>
                            
                            <button type="button" wire:click="submitDeliveryFailure" class="btn-confirm-fail">
                                Confirm Failure
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @script
        <script>
            window.getLocationAndSubmit = function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            $wire.set('gpsLatitude', position.coords.latitude);
                            $wire.set('gpsLongitude', position.coords.longitude);
                            $wire.call('submitDeliverySuccess');
                        },
                        (error) => {
                            console.warn('Geolocation capture failed: ', error);
                            $wire.call('submitDeliverySuccess');
                        },
                        { timeout: 5000 }
                    );
                } else {
                    $wire.call('submitDeliverySuccess');
                }
            };
        </script>
        @endscript
    </div>
</x-filament-panels::page>