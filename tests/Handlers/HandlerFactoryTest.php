<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Tests\Handlers;

use Ely\SkinsRenderer\Handlers;
use Ely\SkinsRenderer\Handlers\HandlerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @covers \Ely\SkinsRenderer\Handlers\HandlerFactory
 */
class HandlerFactoryTest extends TestCase {

    /**
     * @covers \Ely\SkinsRenderer\Handlers\HandlerFactory::createFromRequest
     * @dataProvider getCases
     */
    public function testCreateFromRequest(string $inputUrl, string $handlerName) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $result = HandlerFactory::createFromRequest($this->createRequest($inputUrl));
        $this->assertInstanceOf($handlerName, $result);
    }

    /**
     * @covers \Ely\SkinsRenderer\Handlers\HandlerFactory::createFromRequest
     * @expectedException \Ely\SkinsRenderer\Exceptions\UnknownUrlException
     */
    public function testCreateFromRequestUnknownUrl() {
        HandlerFactory::createFromRequest($this->createRequest('/unknown-url'));
    }

    public function getCases() {
        yield ['/', Handlers\RenderSkinHandler::class];
    }

    /**
     * @param string $inputUrl
     * @return \PHPUnit\Framework\MockObject\MockObject|ServerRequestInterface
     */
    private function createRequest(string $inputUrl) {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn($inputUrl);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        return $request;
    }

}
