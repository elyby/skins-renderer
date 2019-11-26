<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Validators;

class UrlValidator {

    /**
     * @var array
     */
    private $allowedURLs;

    public function __construct(array $allowedURLs) {
        $this->allowedURLs = $allowedURLs;
    }

    public function validate(string $url): bool {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parsedUrl = parse_url($url);
        $path = $parsedUrl['host'] . $parsedUrl['path'];

        foreach ($this->allowedURLs as $allowedPath) {
            if (strpos($path, $allowedPath) === 0) {
                return true;
            }
        }

        return false;
    }

}
