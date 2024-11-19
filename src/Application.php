<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer;

use Ely\SkinsRenderer\Handlers\RenderSkinHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils as Psr7Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final readonly class Application implements RequestHandlerInterface {

    private RequestHandlerInterface $handler;

    public function __construct(
        private bool $debug = false,
    ) {
        $this->handler = RenderSkinHandler::create();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        try {
            return $this->handler->handle($request);
        } catch (Exceptions\InvalidRequestException $e) {
            return $this->buildResponseFromException(400, $e);
        } catch (Throwable $e) {
            return $this->buildResponseFromException(500, $e);
        }
    }

    private function buildResponseFromException(int $statusCode, Throwable $e): ResponseInterface {
        $response = new Response($statusCode);
        if ($this->debug) {
            $message = $e->getMessage();
            $parent = $e->getPrevious();
            while ($parent !== null) {
                $message .= ' CAUSED BY ' . $parent->getMessage();
                $parent = $parent->getPrevious();
            }

            $response = $response->withBody(Psr7Utils::streamFor($message));
        }

        return $response;
    }

}
