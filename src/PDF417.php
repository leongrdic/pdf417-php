<?php

namespace Le\PDF417;

use InvalidArgumentException;
use Le\PDF417\Util\Codes;
use Le\PDF417\Util\ReedSolomon;

/**
 * Constructs a PDF417 barcodes.
 */
class PDF417
{
    public const MIN_COLUMNS = 1;
    public const MAX_COLUMNS = 30;
    public const DEFAULT_COLUMNS = 6;

    public const MIN_SECURITY_LEVEL = 0;
    public const MAX_SECURITY_LEVEL = 8;
    public const DEFAULT_SECURITY_LEVEL = 2;

    // TODO: Check barcode respects rows/codeword limits.
    public const MIN_ROWS = 3;
    public const MAX_ROWS = 90;
    public const MAX_CODE_WORDS = 925;

    public const START_CHARACTER = 0x1fea8;
    public const STOP_CHARACTER  = 0x3fa29;

    public const PADDING_CODE_WORD = 900;


    // -- Properties -----------------------------------------------------------

    /**
     * Number of data columns in the bar code.
     *
     * The total number of columns will be greater due to adding start, stop,
     * left and right columns.
     *
     * Valid values are between 3 and 30, defaults to 6.
     *
     */
    private int $columns = self::DEFAULT_COLUMNS;

    /**
     * The security level to use for Reed Solomon error correction.
     *
     * Valid values are between 0 and 8, defaults to 2.
     */
    private int $securityLevel = self::DEFAULT_SECURITY_LEVEL;

    /**
     * Can be used to force binary encoding. This may reduce size of the
     * barcode if the data contains many encoder changes, such as when
     * encoding a compressed file.
     */
    private bool $forceBinaryEncoding = false;

    // -- Accessors ------------------------------------------------------------

    /**
     * Returns the column count.
     */
    public function getColumns(): int
    {
        return $this->columns;
    }

    /**
     * Sets the column count.
     */
    public function setColumns(int $columns): void
    {
        $min = self::MIN_COLUMNS;
        $max = self::MAX_COLUMNS;

        if ($columns < $min || $columns > $max) {
            throw new InvalidArgumentException("Column count must be between $min and $max. Given: $columns");
        }

        $this->columns = $columns;
    }

    /**
     * Returns the security level.
     */
    public function getSecurityLevel(): int
    {
        return $this->securityLevel;
    }

    /**
     * Sets the security level.
     */
    public function setSecurityLevel(int $securityLevel): void
    {
        $min = self::MIN_SECURITY_LEVEL;
        $max = self::MAX_SECURITY_LEVEL;

        if ($securityLevel < $min || $securityLevel > $max) {
            throw new InvalidArgumentException("Security level must be between $min and $max. Given: $securityLevel");
        }

        $this->securityLevel = $securityLevel;
    }

    /**
     * Returns whether the binary encoding is forced or not.
     */
    public function getForceBinary(): bool
    {
        return $this->forceBinaryEncoding;
    }
    /**
     * Force or not the binary encoding for the whole data.
     */
    public function setForceBinary(bool $force = true): void
    {
        $this->forceBinaryEncoding = $force;
    }

    // -------------------------------------------------------------------------

    /**
     * Encodes the given data to low level code words.
     */
    public function encode(string $data): BarcodeData
    {
        $codeWords = $this->encodeData($data);
        $secLev = $this->securityLevel;
        $columns = $this->columns;

        // Arrange codewords into a rows and columns
        $grid = array_chunk($codeWords, $columns);
        $rows = count($grid);

        // Iterate over rows
        $codes = [];
        foreach ($grid as $rowNum => $row) {
            $table = $rowNum % 3;
            $rowCodes = [];

            // Add starting code word
            $rowCodes[] = self::START_CHARACTER;

            // Add left-side code word
            $left = $this->getLeftCodeWord($rowNum, $rows, $columns, $secLev);
            $rowCodes[] = Codes::getCode($table, $left);

            // Add data code words
            foreach ($row as $word) {
                $rowCodes[] = Codes::getCode($table, $word);
            }

            // Add right-side code word
            $right = $this->getRightCodeWord($rowNum, $rows, $columns, $secLev);
            $rowCodes[] = Codes::getCode($table, $right);

            // Add ending code word
            $rowCodes[] = self::STOP_CHARACTER;

            $codes[] = $rowCodes;
        }

        $data = new BarcodeData();
        $data->codes = $codes;
        $data->rows = $rows;
        $data->columns = $columns;
        $data->codeWords = $codeWords;
        $data->securityLevel = $secLev;

        return $data;
    }

    /** Encodes data to a grid of codewords for constructing the barcode. */
    public function encodeData(string $data): array
    {
        $columns = $this->columns;
        $secLev = $this->securityLevel;

        // Encode data to code words
        $encoder = new DataEncoder($this->forceBinaryEncoding);
        $dataWords = $encoder->encode($data);

        // Number of code correction words
        $ecCount = pow(2, $secLev + 1);
        $dataCount = count($dataWords);

        // Add padding if needed
        $padWords = $this->getPadding($dataCount, $ecCount, $columns);
        $dataWords = array_merge($dataWords, $padWords);

        // Add length specifier as the first data code word
        // Length includes the data CWs, padding CWs and the specifier itself
        $length = count($dataWords) + 1;
        array_unshift($dataWords, $length);

        // Compute error correction code words
        $reedSolomon = new ReedSolomon();
        $ecWords = $reedSolomon->compute($dataWords, $secLev);

        // Combine the code words and return
        return array_merge($dataWords, $ecWords);
    }

    // -------------------------------------------------------------------------

    private function getLeftCodeWord(int $rowNum, int $rows, int $columns, int $secLev): float|int
    {
        // Table used to encode this row
        $tableID = $rowNum % 3;

        switch($tableID) {
            case 0:
                $x = intval(($rows - 1) / 3);
                break;
            case 1:
                $x = $secLev * 3;
                $x += ($rows - 1) % 3;
                break;
            case 2:
                $x = $columns - 1;
                break;
        }

        return 30 * intval($rowNum / 3) + $x;
    }

    private function getRightCodeWord(int $rowNum, int $rows, int $columns, int $secLev): float|int
    {
        $tableID = $rowNum % 3;

        switch($tableID) {
            case 0:
                $x = $columns - 1;
                break;
            case 1:
                $x = intval(($rows - 1) / 3);
                break;
            case 2:
                $x = $secLev * 3;
                $x += ($rows - 1) % 3;
                break;
        }

        return 30 * intval($rowNum / 3) + $x;
    }

    private function getPadding(int $dataCount, int $ecCount, int $columns): array
    {
        // Total number of data words and error correction words, additionally
        // reserve 1 code word for the length descriptor
        $totalCount = $dataCount + $ecCount + 1;
        $mod = $totalCount % $columns;

        if ($mod > 0) {
            $padCount = $columns - $mod;
            $padding = array_fill(0, $padCount, self::PADDING_CODE_WORD);
        } else {
            $padding = [];
        }

        return $padding;
    }
}
