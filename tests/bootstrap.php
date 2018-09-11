<?php
declare(strict_types=1);

use AspectMock\Kernel;

include __DIR__ . '/../vendor/autoload.php';

$kernel = Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'cacheDir' => __DIR__ . '/_aop_cache',
    'includePaths' => [__DIR__ . '/../src'],
]);
