<?php

namespace Le\PDF417;

use Exception;
use Le\PDF417\Encoder\EncoderInterface;

/**
 * Encodes data into PDF417 code words.
 *
 * This is the top level data encoder which assigns encoding to lower level
 * (byte, number, text) encoders.
 */
class DataEncoder
{
    private array $encoders;
    private EncoderInterface $defaultEncoder;
    private bool $forceBinary;

    public function __construct($forceBinary = false)
    {
        // Encoders sorted in order of preference
        $this->encoders = [
            new Encoder\NumberEncoder(),
            new Encoder\TextEncoder(),
            new Encoder\ByteEncoder(),
        ];

        // Default mode is Text
        $this->defaultEncoder = $this->encoders[1];

        $this->forceBinary = $forceBinary;
    }

    /**
     * Encodes given data into an array of PDF417 code words.
     *
     * Splits the input data into chains which can be encoded within the same
     * encoder. Then encodes each chain.
     *
     * Uses a pretty dumb algorithm: switches to the best possible encoder for
     * each separate character (the one that encodes it to the least bytes).
     *
     * TODO: create a better algorithm
     */
    public function encode(string $data): array
    {
        $chains = $this->splitToChains($data);

        // Add a switch code at the beginning if the first encoder to be used
        // is not the text encoder. Decoders by default start decoding as text.
        $firstEncoder = $chains[0][1];
        $addSwitchCode = (!($firstEncoder instanceof Encoder\TextEncoder));

        $codes = [];
        foreach ($chains as $chEnc) {
            list($chain, $encoder) = $chEnc;

            $encoded = $encoder->encode($chain, $addSwitchCode);
            foreach ($encoded as $code) {
                $codes[] = $code;
            }

            $addSwitchCode = true;
        }

        return $codes;
    }

    /**
     * Splits a string into chains (sub-strings) which can be encoded with the
     * same encoder.
     *
     * TODO: Currently always switches to the best encoder, even if it's just
     * for one character, consider a better algorithm.
     */
    private function splitToChains(string $data): array
    {
        $chain = '';
        $chains = [];
        $encoder = $this->defaultEncoder;

        $length = strlen($data);
        for ($i = 0; $i < $length; $i++) {
            $char = $data[$i];

            $newEncoder = $this->getEncoder($char);
            if ($newEncoder !== $encoder) {
                // Save & reset chain if not empty
                if (!empty($chain)) {
                    $chains[] = [$chain, $encoder];
                    $chain = '';
                }

                $encoder = $newEncoder;
            }

            $chain .= $char;
        }

        if (!empty($chain)) {
            $chains[] = [$chain, $encoder];
        }

        return $chains;
    }

    public function getEncoder(string $char): EncoderInterface
    {
        if ($this->forceBinary) {
            return $this->encoders[2];
        }

        foreach ($this->encoders as $encoder) {
            if ($encoder->canEncode($char)) {
                return $encoder;
            }
        }

        $ord = ord($char);
        throw new Exception("Cannot encode character $char (ASCII $ord)");
    }
}
