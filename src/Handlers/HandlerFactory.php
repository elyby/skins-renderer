<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Handlers;

use Ely\SkinsRenderer\Exceptions\UnknownUrlException;
use Psr\Http\Message\RequestInterface;

class HandlerFactory {

    /**
     * @param RequestInterface $request
     *
     * @return HandlerInterface
     * @throws UnknownUrlException
     */
    public static function createFromRequest(RequestInterface $request): HandlerInterface {
        $url = $request->getUri()->getPath();
        switch ($url) {
            case '/':
                return RenderSkinHandler::create();
        }

        throw new UnknownUrlException($url);
    }

}
