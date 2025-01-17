<?php

namespace Le\PDF417\Renderer;

use InvalidArgumentException;
use Le\PDF417\BarcodeData;

abstract class AbstractRenderer implements RendererInterface
{
    /** Default options array. */
    protected array $options = [];

    public function __construct(array $options = [])
    {
        // Merge options with defaults, ignore options not specified in
        // defaults.
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            }
        }

        $errors = $this->validateOptions();
        if ($errors !== []) {
            $errors = implode("\n", $errors);
            throw new InvalidArgumentException($errors);
        }
    }

    /**
     * Validates the options, throws an Exception on failure.
     *
     * @return array An array of errors, empty if no errors.
     */
    public function validateOptions(): array
    {
        return [];
    }

    /**
     * Returns the MIME content type of the barcode generated by this renderer.
     */
    abstract public function getContentType(): ?string;

    /**
     * Renders the barcode from the given data set.
     *
     * @param  BarcodeData $data  The barcode data.
     * @return mixed              Output format depends on the renderer.
     */
    abstract public function render(BarcodeData $data): mixed;
}
