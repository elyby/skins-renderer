<?php
declare(strict_types=1);

use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return \Ely\CS\Config::create()
    ->setFinder($finder);
