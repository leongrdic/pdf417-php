<?php

namespace Le\PDF417\Encoder;

use Exception;

interface EncoderInterface
{
    /**
     * Checks whether the given character can be encoded using this encoder.
     *
     * @param string $char The character.
     */
    public function canEncode(mixed $char): bool;

    /**
     * Encodes a string into codewords.
     *
     * @param string $data        String to encode.
     * @param boolean $addSwitchCode Whether to add the mode switch code at the
     *                                beginning.
     * @return array                  An array of code words.
     * @throws Exception              If any of the characters cannot be encoded
     */
    public function encode(mixed $data, bool $addSwitchCode): array;

    /**
     * Returns the switch code word for the encoding mode implemented by the
     * encoder.
     *
     * @param string $data Data being encoded (can make a difference).
     * @return integer      The switch code word.
     */
    public function getSwitchCode(mixed $data): int;
}
