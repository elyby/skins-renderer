<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Tests\Handlers;

use Ely\SkinsRenderer\Exceptions\UnknownUrlException;
use Ely\SkinsRenderer\Handlers;
use Ely\SkinsRenderer\Handlers\HandlerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @covers \Ely\SkinsRenderer\Handlers\HandlerFactory
 */
final class HandlerFactoryTest extends TestCase {

    /**
     * @covers \Ely\SkinsRenderer\Handlers\HandlerFactory::createFromRequest
     * @dataProvider getCases
     */
    public function testCreateFromRequest(string $inputUrl, string $handlerName): void {
        /** @noinspection PhpUnhandledExceptionInspection */
        $result = HandlerFactory::createFromRequest($this->createRequest($inputUrl));
        $this->assertInstanceOf($handlerName, $result);
    }

    /**
     * @covers \Ely\SkinsRenderer\Handlers\HandlerFactory::createFromRequest
     */
    public function testCreateFromRequestUnknownUrl(): void {
        $this->expectException(UnknownUrlException::class);
        HandlerFactory::createFromRequest($this->createRequest('/unknown-url'));
    }

    public function getCases() {
        yield ['/', Handlers\RenderSkinHandler::class];
    }

    /**
     * @param string $inputUrl
     * @return \PHPUnit\Framework\MockObject\MockObject|ServerRequestInterface
     */
    private function createRequest(string $inputUrl): ServerRequestInterface {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn($inputUrl);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        return $request;
    }

}
