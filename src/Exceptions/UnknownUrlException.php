<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Exceptions;

use Exception;

final class UnknownUrlException extends Exception implements SkinsRendererException {

    private string $url;

    public function __construct(string $url, int $code = 0, \Throwable $previous = null) {
        parent::__construct('Unknown url', $code, $previous);
        $this->url = $url;
    }

    public function getUrl(): string {
        return $this->url;
    }

}
