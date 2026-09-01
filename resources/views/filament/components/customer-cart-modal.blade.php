<div class="w-full py-1 space-y-4">
    @if(!$items || $items->isEmpty())
        <div class="w-full p-8 text-center bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-400">This customer currently has no items in their cart.</p>
        </div>
    @else
        <div class="w-full overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm bg-white dark:bg-gray-900">
            <table class="w-full text-left border-collapse text-sm text-gray-800 dark:text-gray-200">
                <thead class="bg-gray-50/80 dark:bg-gray-800/80 text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold text-xs border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-left">Product</th>
                        <th scope="col" class="px-5 py-3.5 text-left">Variant / Size</th>
                        <th scope="col" class="px-5 py-3.5 text-right">Unit Price</th>
                        <th scope="col" class="px-5 py-3.5 text-center">Qty</th>
                        <th scope="col" class="px-5 py-3.5 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @php $grandTotal = 0; @endphp
                    @foreach($items as $item)
                        @php
                            $variant = $item->variant;
                            $product = $item->product;
                            $unitPrice = (float) ($variant->selling_price ?? 0);
                            $lineTotal = $unitPrice * $item->quantity;
                            $grandTotal += $lineTotal;
                            $sizeLabel = trim(($variant->size ?? '') . ($variant->unit ? ' ' . $variant->unit : ''));
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-5 py-4 align-middle">
                                <div class="flex items-center gap-3.5">
                                  {{--  @if($product && $product->featured_image)
                                        <img src="{{ asset('storage/' . $product->featured_image) }}" alt="{{ $product->name }}" class="w-auto max-h-[100px] object-contain rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shrink-0 p-1" /> 
                                    @else <div> @endif --}}
                                        <div class="w-12 h-12 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-xs font-semibold text-gray-400 shrink-0">
                                            N/A
                                        </div>
                                    
                                    <div class="min-w-0">
                                        <div class="font-medium text-gray-900 dark:text-white text-sm leading-snug">{{ $product->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 font-normal mt-0.5">{{ $product->brand->name ?? 'Premium Essence' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-left align-middle whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                    {{ $sizeLabel ?: 'Standard Edition' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right align-middle whitespace-nowrap font-medium text-gray-700 dark:text-gray-300 text-sm">
                                {{ number_format($unitPrice, 2) }} AED
                            </td>
                            <td class="px-5 py-4 text-center align-middle whitespace-nowrap">
                                <span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-0.5 rounded-md text-xs font-semibold bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                    {{ $item->quantity }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right align-middle whitespace-nowrap font-semibold text-gray-900 dark:text-white text-sm">
                                {{ number_format($lineTotal, 2) }} AED
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50/80 dark:bg-gray-800/80 border-t-2 border-gray-200 dark:border-gray-700">
                    <tr>
                        <td colspan="4" class="px-5 py-4 text-right uppercase tracking-wider text-xs font-semibold text-gray-500 dark:text-gray-400">Total Cart Value:</td>
                        <td class="px-5 py-4 text-right text-base font-bold text-amber-600 dark:text-amber-400 whitespace-nowrap">
                            {{ number_format($grandTotal, 2) }} AED
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
