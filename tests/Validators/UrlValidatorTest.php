<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Tests\Validators;

use Ely\SkinsRenderer\Validators\UrlValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UrlValidator::class)]
final class UrlValidatorTest extends TestCase {

    #[DataProvider('getValidateCases')]
    public function testValidate(string $url, array $allowedUrls, bool $expectedResult): void {
        $validator = new UrlValidator($allowedUrls);
        $result = $validator->validate($url);
        $this->assertSame($expectedResult, $result);
    }

    public static function getValidateCases(): iterable {
        yield 'valid link, valid domain' => [
            'http://example.com/valid.url',
            ['example.com'],
            true,
        ];
        yield 'valid link, valid url' => [
            'https://example.com/valid.url',
            ['example.org', 'example.com/valid'],
            true,
        ];
        yield 'with empty allowed urls' => [
            'http://example.com/test/potato.pic',
            [],
            false,
        ];
        yield 'with invalid url' => [
            'dont.have.http/potato.png',
            ['example.com'],
            false,
        ];
        yield 'valid link, invalid path' => [
            'http://example.com/invalid.path',
            ['example.com/valid.path'],
            false,
        ];
    }

}
