<div class="w-full py-1 space-y-4">
    @if(!$items || $items->isEmpty())
        <div class="w-full p-8 text-center bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-400">This customer currently has no items in their cart.</p>
        </div>
    @else

<div class="w-full overflow-x-auto rounded-xl border shadow-sm">

    <table class="w-full min-w-[850px] border-collapse text-sm">

        <thead>
            <tr class="border-b-2">
                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider">
                    Product
                </th>

                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider">
                    Variant / Size
                </th>

                <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider">
                    Unit Price
                </th>

                <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-wider">
                    Qty
                </th>

                <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider">
                    Line Total
                </th>
            </tr>
        </thead>

        <tbody>

            @php $grandTotal = 0; @endphp

            @foreach($items as $item)

                @php
                    $variant = $item->variant;
                    $product = $item->product;

                    $unitPrice = (float) ($variant->selling_price ?? 0);
                    $lineTotal = $unitPrice * $item->quantity;

                    $grandTotal += $lineTotal;

                    $sizeLabel = trim(
                        ($variant->size ?? '') .
                        ($variant->unit ? ' ' . $variant->unit : '')
                    );
                @endphp

                <tr class="border-b transition-opacity hover:opacity-80">

                    {{-- PRODUCT --}}
                    <td class="px-5 py-4 align-middle">
                        <div class="flex items-center gap-4">

                            @if($product && $product->featured_image)

                                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border">
                                    <img
                                        src="{{ asset('storage/' . $product->featured_image) }}"
                                        alt="{{ $product->name }}"
                                        class="max-h-full max-w-full object-contain p-1"
                                    >
                                </div>

                            @else

                                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg border text-xs font-semibold">
                                    N/A
                                </div>

                            @endif

                            <div class="min-w-0">

                                <div class="truncate text-sm font-semibold">
                                    {{ $product->name ?? 'N/A' }}
                                </div>

                                <div class="mt-1 text-xs opacity-60">
                                    {{ $product->brand->name ?? 'Premium Essence' }}
                                </div>

                            </div>

                        </div>
                    </td>


                    {{-- VARIANT --}}
                    <td class="px-5 py-4 align-middle whitespace-nowrap">

                        <span class="inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-medium">
                            {{ $sizeLabel ?: 'Standard Edition' }}
                        </span>

                    </td>


                    {{-- UNIT PRICE --}}
                    <td class="px-5 py-4 text-right align-middle whitespace-nowrap">

                        <span class="font-medium">
                            {{ number_format($unitPrice, 2) }}
                        </span>

                        <span class="ml-1 text-xs opacity-60">
                            AED
                        </span>

                    </td>


                    {{-- QUANTITY --}}
                    <td class="px-5 py-4 text-center align-middle whitespace-nowrap">

                        <span class="inline-flex min-w-[32px] items-center justify-center rounded-md border px-2 py-1 text-xs font-semibold">
                            {{ $item->quantity }}
                        </span>

                    </td>


                    {{-- LINE TOTAL --}}
                    <td class="px-5 py-4 text-right align-middle whitespace-nowrap">

                        <span class="font-semibold">
                            {{ number_format($lineTotal, 2) }}
                        </span>

                        <span class="ml-1 text-xs opacity-60">
                            AED
                        </span>

                    </td>

                </tr>

            @endforeach

        </tbody>


        {{-- TOTAL --}}
        <tfoot>

            <tr class="border-t-2">

                <td
                    colspan="4"
                    class="px-5 py-5 text-right text-xs font-semibold uppercase tracking-wider"
                >
                    Total Cart Value
                </td>

                <td class="px-5 py-5 text-right whitespace-nowrap">

                    <span class="text-lg font-bold">
                        {{ number_format($grandTotal, 2) }}
                    </span>

                    <span class="ml-1 text-xs font-semibold opacity-60">
                        AED
                    </span>

                </td>

            </tr>

        </tfoot>

    </table>

</div>


    @endif
</div>
