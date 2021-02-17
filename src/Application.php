<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer;

use Ely\SkinsRenderer\Handlers\HandlerFactory;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use function RingCentral\Psr7\stream_for;

final class Application {

    private bool $isDebug = false;

    private string $environment = 'prod';

    public function handle(ServerRequestInterface $request): ResponseInterface {
        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            return HandlerFactory::createFromRequest($request)->handle($request);
        } catch (Exceptions\UnknownUrlException $e) {
            return $this->buildResponseFromException(404, $e);
        } catch (Exceptions\InvalidRequestException $e) {
            return $this->buildResponseFromException(400, $e);
        } catch (Exception $e) {
            return $this->buildResponseFromException(500, $e);
        }
    }

    public function isDebug(): bool {
        return $this->isDebug;
    }

    public function setDebug(bool $isDebug): void {
        $this->isDebug = $isDebug;
    }

    public function getEnvironment(): string {
        return $this->environment;
    }

    public function setEnvironment(string $environment): void {
        $this->environment = $environment;
    }

    private function buildResponseFromException(int $statusCode, Exception $e): ResponseInterface {
        $response = new Response($statusCode);
        if ($this->isDebug) {
            $response = $response->withBody(stream_for($e->getMessage()));
        }

        return $response;
    }

}
