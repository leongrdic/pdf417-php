<?php

namespace Le\PDF417;

/**
 * Container class which holds all data needed to render a PDF417 bar code.
 */
final class BarcodeData
{
    public array $codeWords;
    public int $columns;
    public int $rows;
    public array $codes;
    public int $securityLevel;

    public function getPixelGrid(): array
    {
        $pixelGrid = [];
        foreach ($this->codes as $row) {
            $pixelRow = [];
            foreach ($row as $value) {
                $bin = decbin($value);
                $len = strlen($bin);
                for ($i = 0; $i < $len; $i++) {
                    $pixelRow[] = (bool) $bin[$i];
                }
            }
            $pixelGrid[] = $pixelRow;
        }

        return $pixelGrid;
    }
}
