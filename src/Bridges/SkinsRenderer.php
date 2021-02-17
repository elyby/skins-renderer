<?php
declare(strict_types=1);

namespace PHPPM\Bridges;

use Ely\SkinsRenderer\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @codeCoverageIgnore
 */
final class SkinsRenderer implements BridgeInterface {

    private Application $application;

    public function bootstrap($appBootstrap, $env, $debug): void {
        $this->application = new Application();
        $this->application->setDebug($debug);
        $this->application->setEnvironment($env);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        return $this->application->handle($request);
    }

}
