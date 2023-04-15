# PHP PDF417 generator

[![release](http://poser.pugx.org/leongrdic/pdf417/v)](https://packagist.org/packages/leongrdic/pdf417)
[![php-version](http://poser.pugx.org/leongrdic/pdf417/require/php)](https://packagist.org/packages/leongrdic/pdf417)
[![license](http://poser.pugx.org/leongrdic/pdf417/license)](https://packagist.org/packages/leongrdic/pdf417)
[![run-tests](https://github.com/leongrdic/php-pdf417/actions/workflows/test.yml/badge.svg)](https://github.com/leongrdic/php-pdf417/actions/workflows/test.yml)

This is a fork of [pdf417-php library by ihabunek](https://github.com/ihabunek/pdf417-php), attempting to keep it's legacy alive as it appears to be the only open source PDF417 generation library for PHP.
You can see the [main differences](#migration-from-ihabunekpdf417-php) below.
It has been updated a bit mainly fixing a PHP 8.2 incompatibility issue and merging a few PRs.
The idea is to eventually refactor all components and optimize the encoding algorithm.
Thus, any contributions are more than welcome!

## Requirements

- PHP 8.0+
- Extensions:
  - fileinfo
  - bcmath
  - dom
  - gd
  - simplexml (for running tests)

## Installation

```shell
composer require leongrdic/pdf417
```

## Usage

```php
$pdf417 = new \Le\PDF417\PDF417;
$pdf417->setColumns(15); // optionally set the number of columns
$pdf417->setSecurityLevel(4); // optionally set the security level
$pdf417->setForceBinary(); // optionally force binary encoding

$content = 'Lorem ipsum dolor sit amet.';
$data = $pdf417->encode($content);


$imageRenderer = new \Le\PDF417\Renderer\ImageRenderer([
    // below are default values
    'format' => 'png', // jpg, png, gif, tif, bmp or data-url
    'quality' => 90, // jpeg quality 1-100
    'scale' => 3, // elements scale 1-20
    'ratio' => 3, // height to width aspect 1-10
    'padding' => 20, // padding in px 0-50
    'color' => '#000000', // elements color hex code
    'bgColor' => '#ffffff', // background color hex code
]);

$image = ->render($data)->render($data);
$image instanceof \Intervention\Image\Image; // true


$svgRenderer = new \Le\PDF417\Renderer\SvgRenderer([
    // below are default values
    'scale' => 3, // elements scale 1-20
    'ratio' => 3, // height to width aspect 1-10
    'color' => 'black', // elements color
]);

$svg = $svgRenderer->render($data);
```

### Migration from [ihabunek/pdf417-php](https://github.com/ihabunek/pdf417-php)

- Notable namespace changes
```php
BigFish\PDF417\PDF417                   =>  Le\PDF417\PDF417
BigFish\PDF417\Renderers\ImageRenderer  =>  Le\PDF417\Renderer\ImageRenderer
BigFish\PDF417\Renderers\SvgRenderer    =>  Le\PDF417\Renderer\SvgRenderer
```

- Added types to all properties and method parameters and returns

## Contributions

Please help this library by testing it and submitting PRs for any fixes or improvement. Thank you!