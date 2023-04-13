<?php

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'single_quote' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
        ->exclude('vendor')
        ->in(__DIR__ . '/src')
        ->in(__DIR__ . '/tests')
    );
    