<?php

namespace App\Services\Import;

class PriceCalculator
{
    /**
     * Calculate the selling price from buying price and margin percentage.
     * GST should not be applied here because the Excel margin already includes GST.
     *
     * Formula:
     *   selling_price = buying_price × (1 + margin / 100)
     *
     * @param float $buyingPrice
     * @param float $marginPercent
     * @return float Rounded to 2 decimal places
     */
    public function calculate(float $buyingPrice, float $marginPercent): float
    {
        $sellingPrice = $buyingPrice * (1 + $marginPercent / 100);

        return round($sellingPrice, 2);
    }

    /**
     * Parse GST string from Excel (e.g. "10%", "10", "0.1") to a numeric percentage value.
     *
     * @param mixed $value
     * @return float
     */
    public function parseGst(mixed $value): float
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }

        $cleaned = str_replace('%', '', trim((string) $value));

        return (float) $cleaned;
    }
}
