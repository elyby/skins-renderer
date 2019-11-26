<?php


namespace Ely\SkinsRenderer\Tests\Validators;

use Ely\SkinsRenderer\Validators\UrlValidator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ely\SkinsRenderer\Validators\UrlValidator
 */
class UrlValidatorTest extends TestCase {

    /**
     * @dataProvider getValidateCases
     * @covers \Ely\SkinsRenderer\Validators\UrlValidator::validate
     */
    public function testValidate(string $url, array $allowedUrls, bool $expectedResult) {
        $validator = new UrlValidator($allowedUrls);
        $result = $validator->validate($url);
        $this->assertSame($expectedResult, $result);
    }

    public function getValidateCases() {
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
