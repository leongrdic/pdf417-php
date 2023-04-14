<?php

namespace Le\PDF417\Tests;

use Le\PDF417\DataEncoder;
use Le\PDF417\Encoder\TextEncoder;
use Le\PDF417\Encoder\NumberEncoder;
use Le\PDF417\Encoder\ByteEncoder;
use PHPUnit\Framework\TestCase;

class DataEncoderTest extends TestCase
{
    public function testStartingSwitchCodeWordIsAddedOnlyForText()
    {
        $encoder = new DataEncoder();

        // When starting with text, the first code word does not need to be the switch
        $result = $encoder->encode('ABC123');
        $this->assertNotEquals(TextEncoder::SWITCH_CODE_WORD, $result[0]);
        $this->assertEquals([1, 89, 902, 1, 223], $result);

        // When starting with numbers, we do need to switch
        $result = $encoder->encode('123ABC');
        $this->assertEquals(NumberEncoder::SWITCH_CODE_WORD, $result[0]);
        $this->assertEquals([902, 1, 223, 900, 1, 89], $result);

        // Also with bytes
        $result = $encoder->encode("\x0B");
        $this->assertEquals(ByteEncoder::SWITCH_CODE_WORD, $result[0]);
        $this->assertEquals([901, 11], $result);

        // Alternate bytes switch code when number of bytes is divisble by 6
        $result = $encoder->encode("\x0B\x0B\x0B\x0B\x0B\x0B");
        $this->assertEquals(ByteEncoder::SWITCH_CODE_WORD_ALT, $result[0]);
        $this->assertEquals([924, 18, 455, 694, 754, 291], $result);
    }
}
