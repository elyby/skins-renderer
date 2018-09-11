<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Tests\Handlers;

use Ely\SkinsRenderer\Exceptions\InvalidRequestException;
use Ely\SkinsRenderer\Handlers\RenderSkinHandler;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ServerException as GuzzleServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \Ely\SkinsRenderer\Handlers\RenderSkinHandler
 */
class RenderSkinHandlerTest extends TestCase {

    /**
     * @var MockHandler
     */
    private $guzzleHandler;

    /**
     * @var RenderSkinHandler
     */
    private $handler;

    /**
     * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    protected function setUp() {
        $this->guzzleHandler = new MockHandler();
        $handler = HandlerStack::create($this->guzzleHandler);
        $client = new GuzzleClient(['handler' => $handler]);
        /** @var RenderSkinHandler|\PHPUnit\Framework\MockObject\MockObject application */
        $this->handler = new RenderSkinHandler($client);
        $this->request = $this->createMock(ServerRequestInterface::class);
    }

    /**
     * @covers \Ely\SkinsRenderer\Handlers\RenderSkinHandler::create
     */
    public function testCreate() {
        $result = RenderSkinHandler::create();
        $this->assertInstanceOf(RenderSkinHandler::class, $result);
    }

    /**
     * @dataProvider getResponseCases
     * @covers \Ely\SkinsRenderer\Handlers\RenderSkinHandler::handle
     */
    public function testHandleWithResponse(
        array $queryParams,
        int $expectedResponseStatus,
        string $expectedResponseBodyPath = null,
        int $skinResponseStatus = null,
        string $skinResponseBody = null
    ) {
        $this->request->method('getQueryParams')->willReturn($queryParams);
        if ($skinResponseStatus !== null) {
            $this->guzzleHandler->append(new GuzzleResponse($skinResponseStatus, [], $skinResponseBody));
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $this->handler->handle($this->request);
        $this->assertSame($expectedResponseStatus, $response->getStatusCode());
        if ($expectedResponseStatus === 200) {
            $this->assertSame('image/png', $response->getHeaderLine('Content-Type'));
            $content = $response->getBody()->getContents();
            $this->assertStringEqualsFile($expectedResponseBodyPath, $content);
        }
    }

    /**
     * @dataProvider getExceptionCases
     * @covers \Ely\SkinsRenderer\Handlers\RenderSkinHandler::handle
     */
    public function testHandleWithException(
        array $queryParams,
        string $expectedException,
        string $expectedExceptionMessage = null,
        int $skinResponseStatus = null,
        string $skinResponseBody = null
    ) {
        $this->expectException($expectedException);
        if ($expectedExceptionMessage !== null) {
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $this->request->method('getQueryParams')->willReturn($queryParams);
        if ($skinResponseStatus !== null) {
            $this->guzzleHandler->append(new GuzzleResponse($skinResponseStatus, [], $skinResponseBody));
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->handler->handle($this->request);
    }

    public function getResponseCases() {
        yield 'success response' => [
            ['url' => 'http://ely.by/char.png'],
            200,
            __DIR__ . '/../data/char-rendered.png',
            200,
            file_get_contents(__DIR__ . '/../data/char.png'),
        ];
        yield 'success response scale@10' => [
            ['url' => 'http://ely.by/char.png', 'scale' => '10'],
            200,
            __DIR__ . '/../data/char-rendered-scale-10.png',
            200,
            file_get_contents(__DIR__ . '/../data/char.png'),
        ];
        yield 'success response with face render' => [
            ['url' => 'http://ely.by/char.png', 'renderFace' => '1'],
            200,
            __DIR__ . '/../data/char-face-rendered.png',
            200,
            file_get_contents(__DIR__ . '/../data/char.png'),
        ];
        yield 'success response with face render scale@10' => [
            ['url' => 'http://ely.by/char.png', 'renderFace' => '1', 'scale' => '10'],
            200,
            __DIR__ . '/../data/char-face-rendered-scale-10.png',
            200,
            file_get_contents(__DIR__ . '/../data/char.png'),
        ];
        yield 'url not allowed' => [
            ['url' => 'http://some-minecraft-resource.com/char.png'],
            403,
        ];
        yield 'skin not found' => [
            ['url' => 'http://ely.by/char.png'],
            404,
            null,
            404,
        ];
    }

    public function getExceptionCases() {
        yield 'url not provided' => [
            [],
            InvalidRequestException::class,
            'Required query params not provided: url',
        ];
        yield 'skin response too big' => [
            ['url' => 'http://ely.by/char.png'],
            InvalidRequestException::class,
            'Provided url responds with too big response',
            200,
            file_get_contents(__DIR__ . '/../data/char-big.png'),
        ];
        yield 'skin load server error' => [
            ['url' => 'http://ely.by/char.png'],
            GuzzleServerException::class,
            null,
            503,
        ];
        yield 'provided url is not a png' => [
            ['url' => 'http://ely.by/char.png'],
            InvalidRequestException::class,
            'Provided url responds with not png file',
            200,
            '<html><head><title>YOLO</title></head><body>Hello world!</body></html>',
        ];
        yield 'provided url is not a skin' => [
            ['url' => 'http://ely.by/char.png'],
            InvalidRequestException::class,
            'Unable to render provided skin url',
            200,
            file_get_contents(__DIR__ . '/../data/char-rendered.png'),
        ];
    }

}
