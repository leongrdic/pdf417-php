<?php

namespace Le\PDF417\Renderer;

use Le\PDF417\BarcodeData;

interface RendererInterface
{
    public function render(BarcodeData $data);
}
