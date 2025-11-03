<?php

namespace App\Concerns;

trait CanFormatCount
{
    /**
     * @var string[]
     */
    private $suffixes = ['', 'K', 'M', 'B', 'T'];

    /**
     * @var int[]
     */
    private $thresholds = [1, 1000, 1000000, 1000000000, 1000000000000];

    /**
     * Formats an integer with suffixes (K, M, B, T).
     */
    protected function formatCount(?int $count): string
    {
        if ($count === 0 || $count === null) {
            return '0';
        }

        $isNegative = $count < 0;
        $absCount = abs($count);

        if ($absCount < 1000) {
            // Now returns the original count, handles negatives correctly
            return (string) $count;
        }

        // Find the appropriate suffix and threshold
        $i = count($this->thresholds) - 1;
        while ($i > 0 && $absCount < $this->thresholds[$i]) {
            $i--;
        }

        // Calculate the raw value scaled by the threshold
        $rawValue = $absCount / $this->thresholds[$i];

        // Calculate the value rounded to 1 decimal place to check for the edge case
        $roundedToOneDecimal = round($rawValue, 1);

        $finalValue = $rawValue;
        $finalPrecision = ($rawValue == floor($rawValue)) ? 0 : 1; // Default precision

        // Check if rounding to 1 decimal place results in 1000 or more (the next magnitude base)
        // This happens for values like 999999, 999999999, etc., when divided by their threshold (1000, 1000000, etc.)
        // Also ensure we are not already at the highest suffix ('T')
        if ($roundedToOneDecimal >= 1000 && $i < count($this->suffixes) - 1) {
            // This is the edge case where we want 999.9 followed by the current suffix
            // Calculate 999.9 by flooring after multiplying by 10 and then dividing by 10.
            $finalValue = floor($rawValue * 10) / 10;
            $finalPrecision = 1; // Always 1 decimal place for this specific edge case format
        }

        // Return the rounded value with the determined precision and the suffix
        // Use number_format to ensure the correct number of decimal places are shown,
        // especially for the edge case (e.g. 999.9).
        // number_format handles rounding correctly based on the specified precision.
        // We use '.' for decimal point and '' for thousands separator.
        $formattedNumber = number_format($finalValue, $finalPrecision, '.', '');

        // Prepend negative sign if necessary
        return ($isNegative ? '-' : '').$formattedNumber.$this->suffixes[$i];
    }
}
