<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HandlerInterface {

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws \Ely\SkinsRenderer\Exceptions\SkinsRendererException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;

}
