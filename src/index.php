<?php
declare(strict_types=1);

use Ely\SkinsRenderer\Application;
use Psr\Http\Server\RequestHandlerInterface;

require __DIR__ . '/../vendor/autoload_runtime.php';

return static function(array $context): RequestHandlerInterface {
    // TODO: provide debug
    return new Application();
};
