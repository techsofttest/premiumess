<x-filament::page>


<style>
        .order-detail-wrapper {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            padding: 1.5rem;
        }

        .order-detail-header {
            background: rgb(255 255 255);
            padding: 1.25rem 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .dark .order-detail-header {
            background: rgb(22 22 23);
        }

        .order-detail-header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .order-detail-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: rgb(17, 24, 39);
        }

        .dark .order-detail-title {
            color: rgb(243, 244, 246);
        }

        .order-detail-status-badges {
            display: flex;
            gap: 0.5rem;
        }

        .order-status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .order-status-paid, .order-status-completed {
            background: rgb(220, 252, 231);
            color: rgb(22, 101, 52);
        }

        .order-status-pending, .order-status-processing {
            background: rgb(254, 243, 199);
            color: rgb(146, 64, 14);
        }

        .order-status-failed, .order-status-cancelled {
            background: rgb(254, 226, 226);
            color: rgb(153, 27, 27);
        }

        .dark .order-status-paid, .dark .order-status-completed {
            background: rgb(20, 83, 45);
            color: rgb(187, 247, 208);
        }

        .dark .order-status-pending, .dark .order-status-processing {
            background: rgb(120, 53, 15);
            color: rgb(253, 224, 71);
        }

        .dark .order-status-failed, .dark .order-status-cancelled {
            background: rgb(127, 29, 29);
            color: rgb(254, 202, 202);
        }

        .order-detail-meta {
            display: flex;
            gap: 1.5rem;
            font-size: 0.813rem;
            color: rgb(107, 114, 128);
        }

        .dark .order-detail-meta {
            color: rgb(156, 163, 175);
        }

        .order-detail-content {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 1rem;
        }

        .order-detail-main-section {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .order-detail-card {
            background: rgb(255 255 255);
            border-radius: 0.5rem;
            padding: 1.25rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .dark .order-detail-card {
            background: rgb(22 22 23);
        }

        .order-detail-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .order-detail-card-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(17, 24, 39);
        }

        .dark .order-detail-card-title {
            color: rgb(243, 244, 246);
        }

        .order-detail-btn {
            padding: 0.375rem 0.75rem;
            border: 1px solid rgb(209, 213, 219);
            background: rgb(22 22 23);
            border-radius: 0.375rem;
            font-size: 0.813rem;
            cursor: pointer;
            color: rgb(17, 24, 39);
            font-weight: 500;
            transition: all 0.15s;
        }

        .dark .order-detail-btn {
            background: rgb(55, 65, 81);
            border-color: rgb(75, 85, 99);
            color: rgb(243, 244, 246);
        }

        .order-detail-btn:hover {
            background: rgb(249, 250, 251);
        }

        .dark .order-detail-btn:hover {
            background: rgb(75, 85, 99);
        }

        .order-detail-btn-primary {
            background: rgb(37, 99, 235);
            color: rgb(22 22 23);
            border-color: rgb(37, 99, 235);
        }

        .dark .order-detail-btn-primary {
            background: rgb(59, 130, 246);
            border-color: rgb(59, 130, 246);
        }

        .order-detail-btn-primary:hover {
            background: rgb(29, 78, 216);
        }

        .dark .order-detail-btn-primary:hover {
            background: rgb(37, 99, 235);
        }

        .order-product-list {
            border: 1px solid rgb(229, 231, 235);
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .dark .order-product-list {
            border-color: rgb(55, 65, 81);
        }

        .order-product-item {
            display: flex;
            gap: 0.75rem;
            padding: 1rem;
            border-bottom: 1px solid rgb(229, 231, 235);
        }

        .dark .order-product-item {
            border-bottom-color: rgb(55, 65, 81);
        }

        .order-product-item:last-child {
            border-bottom: none;
        }

        .order-product-image {
            width: 60px;
            height: 60px;
            border-radius: 0.5rem;
            object-fit: cover;
            border: 1px solid rgb(229, 231, 235);
        }

        .dark .order-product-image {
            border-color: rgb(55, 65, 81);
        }

        .order-product-details {
            flex: 1;
        }

        .order-product-name {
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(17, 24, 39);
            margin-bottom: 0.25rem;
        }

        .dark .order-product-name {
            color: rgb(243, 244, 246);
        }

        .order-product-variant {
            font-size: 0.813rem;
            color: rgb(107, 114, 128);
            margin-bottom: 0.125rem;
        }

        .dark .order-product-variant {
            color: rgb(156, 163, 175);
        }

        .order-product-sku {
            font-size: 0.75rem;
            color: rgb(156, 163, 175);
        }

        .dark .order-product-sku {
            color: rgb(107, 114, 128);
        }

        .order-product-price {
            text-align: right;
        }

        .order-product-quantity {
            font-size: 0.813rem;
            color: rgb(107, 114, 128);
            margin-bottom: 0.25rem;
        }

        .dark .order-product-quantity {
            color: rgb(156, 163, 175);
        }

        .order-product-total {
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(17, 24, 39);
        }

        .dark .order-product-total {
            color: rgb(243, 244, 246);
        }

        .order-detail-summary {
            padding-top: 1rem;
            border-top: 1px solid rgb(229, 231, 235);
        }

        .dark .order-detail-summary {
            border-top-color: rgb(55, 65, 81);
        }

        .order-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 1rem;
            font-size: 0.813rem;
        }

        .order-summary-label {
            color: rgb(107, 114, 128);
        }

        .dark .order-summary-label {
            color: rgb(156, 163, 175);
        }

        .order-summary-value {
            color: rgb(17, 24, 39);
            font-weight: 500;
        }

        .dark .order-summary-value {
            color: rgb(243, 244, 246);
        }

        .order-summary-total {
            background: rgb(249, 250, 251);
            margin: 0.5rem 0 0 0;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
        }

        .dark .order-summary-total {
            background: rgb(55, 65, 81);
        }

        .order-summary-total .order-summary-label,
        .order-summary-total .order-summary-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(17, 24, 39);
        }

        .dark .order-summary-total .order-summary-label,
        .dark .order-summary-total .order-summary-value {
            color: rgb(243, 244, 246);
        }

        .order-info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgb(229, 231, 235);
        }

        .dark .order-info-row {
            border-bottom-color: rgb(55, 65, 81);
        }

        .order-info-row:last-child {
            border-bottom: none;
        }

        .order-info-label {
            font-size: 0.813rem;
            color: rgb(107, 114, 128);
        }

        .dark .order-info-label {
            color: rgb(156, 163, 175);
        }

        .order-info-value {
            font-size: 0.813rem;
            color: rgb(17, 24, 39);
            font-weight: 500;
            text-align: right;
        }

        .dark .order-info-value {
            color: rgb(243, 244, 246);
        }

        .order-address-block {
            font-size: 0.813rem;
            color: rgb(17, 24, 39);
            line-height: 1.6;
        }

        .dark .order-address-block {
            color: rgb(243, 244, 246);
        }

        .order-address-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .order-timeline {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .order-timeline-item {
            display: flex;
            gap: 0.75rem;
        }

        .order-timeline-dot {
            width: 8px;
            height: 8px;
            background: rgb(107, 114, 128);
            border-radius: 50%;
            margin-top: 0.375rem;
            flex-shrink: 0;
        }

        .dark .order-timeline-dot {
            background: rgb(156, 163, 175);
        }

        .order-timeline-content {
            flex: 1;
        }

        .order-timeline-title {
            font-size: 0.813rem;
            font-weight: 500;
            color: rgb(17, 24, 39);
            margin-bottom: 0.25rem;
        }

        .dark .order-timeline-title {
            color: rgb(243, 244, 246);
        }

        .order-timeline-meta {
            font-size: 0.75rem;
            color: rgb(107, 114, 128);
        }

        .dark .order-timeline-meta {
            color: rgb(156, 163, 175);
        }

        .order-note-input {
            width: 100%;
            padding: 0.625rem;
            border: 1px solid rgb(209, 213, 219);
            border-radius: 0.375rem;
            font-size: 0.813rem;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
            background: rgb(22 22 23);
            color: rgb(17, 24, 39);
        }

        .dark .order-note-input {
            background: rgb(55, 65, 81);
            border-color: rgb(75, 85, 99);
            color: rgb(243, 244, 246);
        }

        .order-note-input:focus {
            outline: none;
            border-color: rgb(37, 99, 235);
            box-shadow: 0 0 0 1px rgb(37, 99, 235);
        }

        .dark .order-note-input:focus {
            border-color: rgb(59, 130, 246);
            box-shadow: 0 0 0 1px rgb(59, 130, 246);
        }

        .order-card-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        @media (max-width: 1024px) {
            .order-detail-content {
                grid-template-columns: 1fr;
            }
        }
    </style>





<div class="order-detail-wrapper">

    {{-- HEADER --}}
    <div class="order-detail-header">
        <div class="order-detail-header-top">
            <h1 class="order-detail-title">
                {{ $this->record->order_number }}
            </h1>

            <div class="order-detail-status-badges">
                <span class="order-status-badge order-status-{{ $this->record->status->value }}">
                    {{ ucfirst($this->record->status->value) }}
                </span>
                <span class="order-status-badge order-status-{{ $this->record->payment_status->value }}">
                    {{ ucfirst($this->record->payment_status->value) }}
                </span>
            </div>
        </div>

        <div class="order-detail-meta">
            <span>
                {{ $this->record->created_at?->format('F j, Y \a\t g:i A') }}
            </span>
            <span>•</span>
            <span>{{ $this->record->items->count() }} items</span>
        </div>
    </div>

    <div class="order-detail-content">

        {{-- LEFT COLUMN --}}
        <div class="order-detail-main-section">

            {{-- PRODUCTS --}}
            <div class="order-detail-card">
                <div class="order-detail-card-header">
                    <h2 class="order-detail-card-title">Products</h2>
                </div>

                <div class="order-product-list">
                    @foreach ($this->record->items as $item)
                        <div class="order-product-item">
                            @php
                                $productImage = ($item->product && $item->product->featured_image)
                                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->product->featured_image)
                                    : asset('images/placeholder.png');
                            @endphp
                            <img
                                src="{{ $productImage }}"
                                class="order-product-image"
                                alt="{{ $item->product_name }}"
                            >
                            <div class="order-product-details">
                                <div class="order-product-name">
                                    {{ $item->product_name }}
                                </div>

                                @if ($item->variant_details)
                                    <div class="order-product-variant">
                                        {{ $item->variant_details }}
                                    </div>
                                @endif
                            </div>

                            <div style="align-self: center; margin-right: 1.5rem;">
                                @if ($item->is_picked)
                                    <x-filament::button
                                        type="button"
                                        wire:click="togglePicked({{ $item->id }})"
                                        color="success"
                                        icon="heroicon-m-check"
                                        size="xs"
                                    >
                                        Picked
                                    </x-filament::button>
                                @else
                                    <x-filament::button
                                        type="button"
                                        wire:click="togglePicked({{ $item->id }})"
                                        color="danger"
                                        icon="heroicon-m-x-mark"
                                        size="xs"
                                    >
                                        Not Picked
                                    </x-filament::button>
                                @endif
                            </div>

                            <div class="order-product-price">
                                <div class="order-product-quantity">
                                    {{ $item->quantity }} × ₹{{ number_format($item->price, 2) }}
                                </div>
                                <div class="order-product-total">
                                    ₹{{ number_format($item->line_total, 2) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- SUMMARY --}}
                <div class="order-detail-summary">
                    <div class="order-summary-row">
                        <span>Subtotal</span>
                        <span>${{ number_format($this->record->subtotal, 2) }}</span>
                    </div>

                    <div class="order-summary-row">
                        <span>Shipping</span>
                        <span>${{ number_format($this->record->shipping_cost, 2) }}</span>
                    </div>

                    @if($this->record->discount > 0)
                    <div class="order-summary-row">
                        <span>Discount ({{ $this->record->coupon_code }})</span>
                        <span style="color: #d32f2f;">-${{ number_format($this->record->discount, 2) }}</span>
                    </div>
                    @endif

                    <div class="order-summary-row order-summary-total">
                        <span>Total</span>
                        <span>${{ number_format($this->record->grand_total, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- PAYMENT --}}
            <div class="order-detail-card">
                <div class="order-detail-card-header">
                    <h2 class="order-detail-card-title">Payment</h2>
                </div>

                <div class="order-info-row">
                    <span>Payment method</span>
                    <span>{{ strtoupper($this->record->payment_method) }}</span>
                </div>

                <div class="order-info-row">
                    <span>Status</span>
                    <span>{{ ucfirst($this->record->payment_status->value) }}</span>
                </div>

                <div class="order-info-row">
                    <span>Amount</span>
                    <span>${{ number_format($this->record->grand_total, 2) }}</span>
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="order-detail-main-section">

            {{-- CUSTOMER --}}
            <div class="order-detail-card">
                <div class="order-detail-card-header">
                    <h2 class="order-detail-card-title">Customer</h2>
                </div>

                <div class="order-info-row">
                    <span>Name</span>
                    <span>{{ $this->record->first_name }} {{ $this->record->last_name }}</span>
                </div>

                <div class="order-info-row">
                    <span>Email</span>
                    <span>{{ $this->record->email ?? $this->record->customer_email ?? $this->record->customer?->email ?? '—' }}</span>
                </div>

                <div class="order-info-row">
                    <span>Phone</span>
                    <span>{{ $this->record->phone }}</span>
                </div>
            </div>

            {{-- SHIPPING ADDRESS --}}
            <div class="order-detail-card">
                <div class="order-detail-card-header">
                    <h2 class="order-detail-card-title">Address</h2>
                </div>

                <div class="order-address-block">
                    <div class="order-address-name">{{ $this->record->first_name }} {{ $this->record->last_name }}</div>
                    <div>{{ $this->record->address }}</div>
                    @if($this->record->apartment)
                        <div>{{ $this->record->apartment }}</div>
                    @endif
                    <div>{{ $this->record->city }}, {{ $this->record->state }}, {{ $this->record->country }}</div>
                    <div>{{ $this->record->pin_code }}</div>
                </div>
            </div>

            {{-- BILLING ADDRESS 
            <div class="order-detail-card">
                <div class="order-detail-card-header">
                    <h2 class="order-detail-card-title">Billing address</h2>
                </div>

                @if($this->record->billing_details)
                    @php($billing = $this->record->billing_details)
                    <div class="order-address-block">
                        <div class="order-address-name">{{ $billing['first_name'] ?? '' }} {{ $billing['last_name'] ?? '' }}</div>
                        <div>{{ $billing['address'] ?? '' }}</div>
                        @if(!empty($billing['apartment']))
                            <div>{{ $billing['apartment'] }}</div>
                        @endif
                        <div>{{ $billing['city'] ?? '' }}, {{ $billing['state'] ?? '' }}, {{ $billing['country'] ?? '' }}</div>
                        <div>{{ $billing['pin_code'] ?? '' }}</div>
                    </div>
                @else
                    <div class="order-address-block text-muted" style="font-size: 11px;">
                        Same as shipping address
                    </div>
                @endif
            </div>

            --}}



        </div>
    </div>
</div>

                <!-- Timeline Card -->
                <!--
                <div class="order-detail-card">
                    <div class="order-detail-card-header">
                        <h2 class="order-detail-card-title">Timeline</h2>
                    </div>
                    <div class="order-timeline">
                        <div class="order-timeline-item">
                            <div class="order-timeline-dot"></div>
                            <div class="order-timeline-content">
                                <div class="order-timeline-title">Order placed</div>
                                <div class="order-timeline-meta">November 6, 2025 at 2:45 PM</div>
                            </div>
                        </div>

                        <div class="order-timeline-item">
                            <div class="order-timeline-dot"></div>
                            <div class="order-timeline-content">
                                <div class="order-timeline-title">Payment received</div>
                                <div class="order-timeline-meta">November 6, 2025 at 2:45 PM</div>
                            </div>
                        </div>

                        <div class="order-timeline-item">
                            <div class="order-timeline-dot"></div>
                            <div class="order-timeline-content">
                                <div class="order-timeline-title">Order confirmation sent</div>
                                <div class="order-timeline-meta">November 6, 2025 at 2:46 PM</div>
                            </div>
                        </div>

                    </div>
                </div>
                -->










    



</x-filament::page>
