<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Validators;

final readonly class UrlValidator {

    /**
     * @param list<string> $allowedURLs
     */
    public function __construct(
        private array $allowedURLs,
    ) {
    }

    public function validate(string $url): bool {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parsedUrl = parse_url($url);
        $path = $parsedUrl['host'] . $parsedUrl['path'];

        foreach ($this->allowedURLs as $allowedPath) {
            if (str_starts_with($path, $allowedPath)) {
                return true;
            }
        }

        return false;
    }

}
