<?php
declare(strict_types = 1);

namespace Vietstars\Base58;

use Vietstars\Base58;

class PhpEncoder extends BaseEncoder
{
    /**
     * Convert an integer between artbitrary bases
     */
    public function baseConvert(array $source, int $sourceBase, int $targetBase): array
    {
        $result = [];
        while ($count = count($source)) {
            $quotient = [];
            $remainder = 0;
            for ($i = 0; $i !== $count; $i++) {
                $accumulator = $source[$i] + $remainder * $sourceBase;
                /* Same as PHP 7 intdiv($accumulator, $targetBase) */
                $digit = ($accumulator - ($accumulator % $targetBase)) / $targetBase;
                $remainder = $accumulator % $targetBase;
                if (count($quotient) || $digit) {
                    $quotient[] = $digit;
                }
            }
            array_unshift($result, $remainder);
            $source = $quotient;
        }

        return $result;
    }
}
