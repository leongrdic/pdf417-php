<?php

namespace Le\PDF417\Encoder;

use InvalidArgumentException;

/**
 * Converts numbers to code words.
 *
 * Can encode: digits 0-9
 * Rate: 2.9 digits per code word.
 */
class NumberEncoder implements EncoderInterface
{
    /**
     * Code word used to switch to Numeric mode.
     */
    public const SWITCH_CODE_WORD = 902;

    public function canEncode(mixed $char): bool
    {
        return is_string($char) && 1 === preg_match('/^[0-9]$/', $char);
    }

    public function getSwitchCode(mixed $data): int
    {
        return self::SWITCH_CODE_WORD;
    }

    /**
     * The "Numeric" mode is a conversion from base 10 to base 900.
     *
     * - numbers are taken in groups of 44 (or less)
     * - digit "1" is added to the beginning of the group (it will later be
     *   removed by the decoding procedure)
     * - base is changed from 10 to 900
     */
    public function encode(mixed $data, bool $addSwitchCode = false): array
    {
        if (!is_string($data)) {
            $type = gettype($data);
            throw new InvalidArgumentException("Expected first parameter to be a string, $type given.");
        }

        if (!preg_match('/^[0-9]+$/', $data)) {
            throw new InvalidArgumentException('First parameter contains non-numeric characters.');
        }

        // Count the number of 44 character chunks
        $digitCount = strlen($data);
        $chunkCount = ceil($digitCount / 44);

        $codeWords = [];

        if ($addSwitchCode) {
            $codeWords[] = self::SWITCH_CODE_WORD;
        }

        // Encode in chunks of 44 digits
        for ($i = 0; $i < $chunkCount; $i++) {
            $chunk = substr($data, $i * 44, 44);

            $cws = $this->encodeChunk($chunk);

            // Avoid using array_merge
            foreach ($cws as $cw) {
                $codeWords[] = $cw;
            }
        }

        return $codeWords;
    }

    private function encodeChunk(string $chunk): array
    {
        $chunk = '1' . $chunk;

        $cws = [];
        while(bccomp($chunk, 0) > 0) {
            $cw = bcmod($chunk, 900);
            $chunk = bcdiv($chunk, 900); // Integer division

            array_unshift($cws, (int) $cw);
        }

        return $cws;
    }
}
