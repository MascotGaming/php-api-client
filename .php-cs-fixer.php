<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setRules([
        'phpdoc_align' => true,
        'phpdoc_separation' => true,
    ])
    ->setFinder($finder);
