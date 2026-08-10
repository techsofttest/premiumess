<x-filament-panels::page>
    <div>
    @php
        $stats = $this->getStats();
    @endphp

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Today's Orders -->
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-5 text-white shadow-lg relative overflow-hidden transition-all duration-300 hover:scale-105">
            <div class="absolute -right-6 -bottom-6 opacity-10">
                <svg class="w-32 h-32" width="128" height="128" style="width: 128px; height: 128px;" fill="currentColor" viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
            </div>
            <div class="text-sm font-semibold tracking-wider uppercase opacity-75">Today's Orders</div>
            <div class="text-3xl font-bold mt-2">{{ $stats['today_orders'] }}</div>
            <div class="text-xs mt-3 opacity-90">Created in system today</div>
        </div>

        <!-- Ready for Dispatch -->
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-5 text-white shadow-lg relative overflow-hidden transition-all duration-300 hover:scale-105">
            <div class="absolute -right-6 -bottom-6 opacity-10">
                <svg class="w-32 h-32" width="128" height="128" style="width: 128px; height: 128px;" fill="currentColor" viewBox="0 0 24 24"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm12 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
            </div>
            <div class="text-sm font-semibold tracking-wider uppercase opacity-75">Ready for Dispatch</div>
            <div class="text-3xl font-bold mt-2">{{ $stats['ready_for_dispatch'] }}</div>
            <div class="text-xs mt-3 opacity-90">Paid & packed orders</div>
        </div>

        <!-- Morning Session -->
        <div class="bg-gradient-to-br from-cyan-500 to-teal-600 rounded-xl p-5 text-white shadow-lg relative overflow-hidden transition-all duration-300 hover:scale-105">
            <div class="absolute -right-6 -bottom-6 opacity-10">
                <svg class="w-32 h-32" width="128" height="128" style="width: 128px; height: 128px;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.53c-.26-.81-1-1.4-1.9-1.4h-1v-3c0-.55-.45-1-1-1h-6v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
            </div>
            <div class="text-sm font-semibold tracking-wider uppercase opacity-75">Morning Session</div>
            <div class="text-3xl font-bold mt-2">{{ $stats['morning_session'] }}</div>
            <div class="text-xs mt-3 opacity-90">Deliveries before 12:00 PM</div>
        </div>

        <!-- Afternoon Session -->
        <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-5 text-white shadow-lg relative overflow-hidden transition-all duration-300 hover:scale-105">
            <div class="absolute -right-6 -bottom-6 opacity-10">
                <svg class="w-32 h-32" width="128" height="128" style="width: 128px; height: 128px;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.53c-.26-.81-1-1.4-1.9-1.4h-1v-3c0-.55-.45-1-1-1h-6v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
            </div>
            <div class="text-sm font-semibold tracking-wider uppercase opacity-75">Afternoon Session</div>
            <div class="text-3xl font-bold mt-2">{{ $stats['afternoon_session'] }}</div>
            <div class="text-xs mt-3 opacity-90">Deliveries after 12:00 PM</div>
        </div>
    </div>

    <!-- Mini Sub-stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl p-4 flex items-center justify-between shadow-sm">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">Delivered Today</div>
                <div class="text-2xl font-bold text-success-600 mt-1">{{ $stats['delivered'] }}</div>
            </div>
            <div class="p-3 bg-success-50 dark:bg-success-950 text-success-600 dark:text-success-400 rounded-lg">
                <svg class="w-6 h-6" width="24" height="24" style="width: 24px; height: 24px; display: inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl p-4 flex items-center justify-between shadow-sm">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">Pending Deliveries</div>
                <div class="text-2xl font-bold text-warning-600 mt-1">{{ $stats['pending'] }}</div>
            </div>
            <div class="p-3 bg-warning-50 dark:bg-warning-950 text-warning-600 dark:text-warning-400 rounded-lg">
                <svg class="w-6 h-6" width="24" height="24" style="width: 24px; height: 24px; display: inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl p-4 flex items-center justify-between shadow-sm">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">Failed Deliveries Today</div>
                <div class="text-2xl font-bold text-danger-600 mt-1">{{ $stats['failed'] }}</div>
            </div>
            <div class="p-3 bg-danger-50 dark:bg-danger-950 text-danger-600 dark:text-danger-400 rounded-lg">
                <svg class="w-6 h-6" width="24" height="24" style="width: 24px; height: 24px; display: inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Map & Route Section -->
    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl p-6 shadow-sm mt-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Route Optimization Map</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Select a delivery session to view the store, stops, and optimized delivery path.</p>
            </div>
            <div>
                <select wire:model.live="selectedSessionId" class="block w-full md:w-72 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Select Delivery Session --</option>
                    @foreach ($this->sessions as $sess)
                        <option value="{{ $sess->id }}">
                            {{ $sess->delivery_date->format('d M Y') }} ({{ $sess->timeSlot?->start_time }} - {{ $sess->timeSlot?->end_time }}) [{{ ucfirst($sess->status) }}]
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Leaflet Map Container -->
        <div class="relative">
            <div wire:ignore id="delivery-map" style="height: 500px; border-radius: 12px;" class="z-0 border border-gray-200 dark:border-gray-800"></div>
        </div>
    </div>
    </div>
</x-filament-panels::page>
