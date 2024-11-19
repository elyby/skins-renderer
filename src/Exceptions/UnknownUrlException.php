<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Exceptions;

use Exception;
use Throwable;

final class UnknownUrlException extends Exception implements SkinsRendererException {

    public function __construct(
        public readonly string $url,
        int $code = 0,
        Throwable $previous = null,
    ) {
        parent::__construct('Unknown url', $code, $previous);
    }

}
