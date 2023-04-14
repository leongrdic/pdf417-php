<?php

namespace Le\PDF417\Renderer;

use Exception;
use Le\PDF417\BarcodeData;

class JsonRenderer extends AbstractRenderer
{
    public function getContentType(): ?string
    {
        return 'application/json';
    }

    public function render(BarcodeData $data): string
    {
        // Function which translates true/false to 1/0
        $fmap = function ($element) {
            return $element ? 1 : 0;
        };

        // Apply function to the pixel map
        $return = [];
        foreach ($data->getPixelGrid() as $row) {
            $return[] = array_map($fmap, $row);
        }

        $json = json_encode($return);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // This should never happen
            // @codeCoverageIgnoreStart
            $msg = json_last_error_msg();
            throw new Exception("Failed encoding JSON: $msg");
            // @codeCoverageIgnoreEnd
        }

        return $json;
    }
}
