<div class="w-full py-1 space-y-4">
    @if(!$items || $items->isEmpty())
        <div class="w-full p-8 text-center bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-400">This customer currently has no items in their cart.</p>
        </div>
    @else
        ```html
<div style="
    width: 100%;
    overflow-x: auto;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
">

    <table style="
        width: 100%;
        min-width: 850px;
        border-collapse: collapse;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
        color: #374151;
    ">

        <!-- TABLE HEADER -->
        <thead>
            <tr style="
                background: #f8fafc;
                border-bottom: 2px solid #e5e7eb;
            ">
                <th style="
                    padding: 14px 18px;
                    text-align: left;
                    font-size: 12px;
                    font-weight: 700;
                    color: #6b7280;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                ">
                    Product
                </th>

                <th style="
                    padding: 14px 18px;
                    text-align: left;
                    font-size: 12px;
                    font-weight: 700;
                    color: #6b7280;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                ">
                    Variant / Size
                </th>

                <th style="
                    padding: 14px 18px;
                    text-align: right;
                    font-size: 12px;
                    font-weight: 700;
                    color: #6b7280;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                ">
                    Unit Price
                </th>

                <th style="
                    padding: 14px 18px;
                    text-align: center;
                    font-size: 12px;
                    font-weight: 700;
                    color: #6b7280;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                ">
                    Qty
                </th>

                <th style="
                    padding: 14px 18px;
                    text-align: right;
                    font-size: 12px;
                    font-weight: 700;
                    color: #6b7280;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                ">
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

                <tr style="
                    border-bottom: 1px solid #e5e7eb;
                ">

                    <!-- PRODUCT -->
                    <td style="
                        padding: 16px 18px;
                        vertical-align: middle;
                    ">
                        <div style="
                            display: flex;
                            align-items: center;
                            gap: 14px;
                        ">

                            @if($product && $product->featured_image)

                                <div style="
                                    width: 64px;
                                    height: 64px;
                                    min-width: 64px;
                                    border: 1px solid #e5e7eb;
                                    border-radius: 8px;
                                    background: #ffffff;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    overflow: hidden;
                                ">
                                    <img
                                        src="{{ asset('storage/' . $product->featured_image) }}"
                                        alt="{{ $product->name }}"
                                        style="
                                            max-width: 100%;
                                            max-height: 100%;
                                            width: auto;
                                            height: auto;
                                            object-fit: contain;
                                            padding: 4px;
                                            display: block;
                                        "
                                    >
                                </div>

                            @else

                                <div style="
                                    width: 64px;
                                    height: 64px;
                                    min-width: 64px;
                                    border: 1px solid #e5e7eb;
                                    border-radius: 8px;
                                    background: #f3f4f6;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    color: #9ca3af;
                                    font-size: 11px;
                                    font-weight: 600;
                                ">
                                    N/A
                                </div>

                            @endif

                            <div style="
                                min-width: 0;
                            ">

                                <div style="
                                    font-size: 14px;
                                    font-weight: 600;
                                    color: #111827;
                                    line-height: 1.4;
                                    margin-bottom: 3px;
                                ">
                                    {{ $product->name ?? 'N/A' }}
                                </div>

                                <div style="
                                    font-size: 12px;
                                    color: #6b7280;
                                    line-height: 1.4;
                                ">
                                    {{ $product->brand->name ?? 'Premium Essence' }}
                                </div>

                            </div>

                        </div>
                    </td>


                    <!-- VARIANT -->
                    <td style="
                        padding: 16px 18px;
                        vertical-align: middle;
                        white-space: nowrap;
                    ">
                        <span style="
                            display: inline-block;
                            padding: 5px 10px;
                            background: #f3f4f6;
                            border: 1px solid #e5e7eb;
                            border-radius: 5px;
                            font-size: 12px;
                            font-weight: 600;
                            color: #4b5563;
                        ">
                            {{ $sizeLabel ?: 'Standard Edition' }}
                        </span>
                    </td>


                    <!-- UNIT PRICE -->
                    <td style="
                        padding: 16px 18px;
                        text-align: right;
                        vertical-align: middle;
                        white-space: nowrap;
                        font-size: 14px;
                        color: #374151;
                    ">
                        <span style="font-weight: 500;">
                            {{ number_format($unitPrice, 2) }}
                        </span>
                        <span style="
                            font-size: 12px;
                            color: #6b7280;
                            margin-left: 3px;
                        ">
                            AED
                        </span>
                    </td>


                    <!-- QUANTITY -->
                    <td style="
                        padding: 16px 18px;
                        text-align: center;
                        vertical-align: middle;
                        white-space: nowrap;
                    ">
                        <span style="
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            min-width: 30px;
                            height: 28px;
                            padding: 0 8px;
                            border: 1px solid #d1d5db;
                            border-radius: 5px;
                            background: #ffffff;
                            font-size: 13px;
                            font-weight: 600;
                            color: #374151;
                        ">
                            {{ $item->quantity }}
                        </span>
                    </td>


                    <!-- LINE TOTAL -->
                    <td style="
                        padding: 16px 18px;
                        text-align: right;
                        vertical-align: middle;
                        white-space: nowrap;
                        font-size: 14px;
                        font-weight: 700;
                        color: #111827;
                    ">
                        {{ number_format($lineTotal, 2) }}
                        <span style="
                            font-size: 12px;
                            font-weight: 500;
                            color: #6b7280;
                            margin-left: 3px;
                        ">
                            AED
                        </span>
                    </td>

                </tr>

            @endforeach

        </tbody>


        <!-- TOTAL -->
        <tfoot>

            <tr style="
                background: #f8fafc;
            ">

                <td
                    colspan="4"
                    style="
                        padding: 18px;
                        text-align: right;
                        border-top: 2px solid #d1d5db;
                        font-size: 12px;
                        font-weight: 700;
                        color: #6b7280;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                    "
                >
                    Total Cart Value
                </td>

                <td style="
                    padding: 18px;
                    text-align: right;
                    border-top: 2px solid #d1d5db;
                    white-space: nowrap;
                    font-size: 17px;
                    font-weight: 700;
                    color: #b45309;
                ">
                    {{ number_format($grandTotal, 2) }}

                    <span style="
                        font-size: 13px;
                        font-weight: 600;
                        color: #92400e;
                        margin-left: 3px;
                    ">
                        AED
                    </span>
                </td>

            </tr>

        </tfoot>

    </table>

</div>
```

    @endif
</div>
