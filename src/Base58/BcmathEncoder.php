<?php

declare(strict_types = 1);


namespace Vietstars\Base58;

use Vietstars\Base58;

class BcmathEncoder extends BaseEncoder
{
    /**
     * Convert an integer between artbitrary bases
     *
     */
    public function baseConvert(array $source, int $sourceBase, int $targetBase): array
    {
        $result = [];
        while ($count = count($source)) {
            $quotient = [];
            $remainder = "0";
            $sourceBase = (string) $sourceBase;
            $targetBase = (string) $targetBase;
            for ($i = 0; $i !== $count; $i++) {
                $accumulator = bcadd((string) $source[$i], bcmul((string)$remainder, $sourceBase));
                $digit = bcdiv($accumulator, $targetBase, 0);
                $remainder = bcmod($accumulator, $targetBase);
                if (count($quotient) || $digit) {
                    array_push($quotient, $digit);
                };
            }
            array_unshift($result, $remainder);
            $source = $quotient;
        }

        return $result;
    }
}
