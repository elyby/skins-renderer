<?php
declare(strict_types=1);

namespace PHPPM\Bridges;

use Ely\SkinsRenderer\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @codeCoverageIgnore
 */
class SkinsRenderer implements BridgeInterface {

    /**
     * @var Application
     */
    private $application;

    public function bootstrap($appBootstrap, $appEnv, $debug): void {
        $this->application = new Application();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        return $this->application->handle($request);
    }

}
