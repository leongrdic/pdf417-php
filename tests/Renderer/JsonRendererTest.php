<?php

namespace Le\PDF417\Tests\Renderer;

use Le\PDF417\BarcodeData;
use Le\PDF417\Renderer\JsonRenderer;
use PHPUnit\Framework\TestCase;

class JsonRendererTest extends TestCase
{
    public function testContentType()
    {
        $renderer = new JsonRenderer();
        $actual = $renderer->getContentType();
        $expected = 'application/json';
        $this->assertSame($expected, $actual);
    }

    public function testRender()
    {
        $data = new BarcodeData();
        $data->codes = [
            [true, false],
            [false, true],
        ];

        $renderer = new JsonRenderer();
        $actual = $renderer->render($data);
        $expected = '[[1,0],[0,1]]';

        $this->assertSame($expected, $actual);
    }
}
