<?php

declare(strict_types = 1);

namespace Vietstars\Base58;

use InvalidArgumentException;
use RuntimeException;
use Vietstars\Base58;

abstract class BaseEncoder
{
    /**
     * @var mixed[]
     */
    private $options = [
        "characters" => Base58::GMP,
        "check" => false,
        "version" => 0x00,
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, (array) $options);

        $uniques = count_chars($this->options["characters"], 3);
        /** @phpstan-ignore-next-line */
        if (58 !== strlen($uniques) || 58 !== strlen($this->options["characters"])) {
            throw new InvalidArgumentException("Character set must contain 58 unique characters");
        }
    }

    /**
     * Encode given data to a base58 string
     */
    public function encode(string $data): string
    {
        if (true === $this->options["check"]) {
            $data = chr($this->options["version"]) . $data;
            $hash = hash("sha256", $data, true);
            $hash = hash("sha256", $hash, true);
            $checksum = substr($hash, 0, 4);
            $data .= $checksum;
        }

        $data = str_split($data);
        $data = array_map("ord", $data);

        $leadingZeroes = 0;
        while (!empty($data) && 0 === $data[0]) {
            $leadingZeroes++;
            array_shift($data);
        }

        $converted = $this->baseConvert($data, 256, 58);

        if (0 < $leadingZeroes) {
            $converted = array_merge(
                array_fill(0, $leadingZeroes, 0),
                $converted
            );
        }

        return implode("", array_map(function ($index) {
            return $this->options["characters"][$index];
        }, $converted));
    }

    /**
     * Decode given base58 string back to data
     */
    public function decode(string $data): string
    {
        $this->validateInput($data);

        $data = str_split($data);
        $data = array_map(function ($character) {
            return strpos($this->options["characters"], $character);
        }, $data);

        $leadingZeroes = 0;
        while (!empty($data) && 0 === $data[0]) {
            $leadingZeroes++;
            array_shift($data);
        }

        $converted = $this->baseConvert($data, 58, 256);

        if (0 < $leadingZeroes) {
            $converted = array_merge(
                array_fill(0, $leadingZeroes, 0),
                $converted
            );
        }

        $decoded = implode("", array_map("chr", $converted));
        if (true === $this->options["check"]) {
            $hash = substr($decoded, 0, -(Base58::CHECKSUM_SIZE));
            $hash = hash("sha256", $hash, true);
            $hash = hash("sha256", $hash, true);
            $checksum = substr($hash, 0, Base58::CHECKSUM_SIZE);

            if (0 !== substr_compare($decoded, $checksum, -(Base58::CHECKSUM_SIZE))) {
                $message = sprintf(
                    'Checksum "%s" does not match the expected "%s"',
                    bin2hex(substr($decoded, -(Base58::CHECKSUM_SIZE))),
                    bin2hex($checksum)
                );
                throw new RuntimeException($message);
            }

            $version = substr($decoded, 0, Base58::VERSION_SIZE);
            $version = ord($version);

            if ($version !==  $this->options["version"]) {
                $message = sprintf(
                    'Version "%s" does not match the expected "%s"',
                    $version,
                    $this->options["version"]
                );
                throw new RuntimeException($message);
            }

            $decoded = substr($decoded, Base58::VERSION_SIZE, -(Base58::CHECKSUM_SIZE));
        }
        return $decoded;
    }

    /**
     * Encode given integer to a base58 string
     */
    public function encodeInteger(int $data): string
    {
        $data = [$data];

        $converted = $this->baseConvert($data, 256, 58);

        return implode("", array_map(function ($index) {
            return $this->options["characters"][$index];
        }, $converted));
    }

    /**
     * Decode given base58 string back to an integer
     */
    public function decodeInteger(string $data): int
    {
        $this->validateInput($data);

        $data = str_split($data);
        $data = array_map(function ($character) {
            return strpos($this->options["characters"], $character);
        }, $data);

        $converted = $this->baseConvert($data, 58, 10);
        return (integer) implode("", $converted);
    }

    private function validateInput(string $data): void
    {
        /* If the data contains characters that aren't in the character set. */
        if (strlen($data) !== strspn($data, $this->options["characters"])) {
            $valid = str_split($this->options["characters"]);
            $invalid = str_replace($valid, "", $data);
            $invalid = count_chars($invalid, 3);
            throw new InvalidArgumentException(
                /** @phpstan-ignore-next-line */
                "Data contains invalid characters \"{$invalid}\""
            );
        }
    }

    abstract public function baseConvert(array $source, int $sourceBase, int $targetBase): array;
}
