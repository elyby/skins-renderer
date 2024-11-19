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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(RenderSkinHandler::class)]
final class RenderSkinHandlerTest extends TestCase {

    private MockHandler $guzzleHandler;

    private RenderSkinHandler $handler;

    private ServerRequestInterface&MockObject $request;

    protected function setUp(): void {
        $this->guzzleHandler = new MockHandler();
        $handler = HandlerStack::create($this->guzzleHandler);
        $client = new GuzzleClient(['handler' => $handler]);
        $this->handler = new RenderSkinHandler($client);
        $this->request = $this->createMock(ServerRequestInterface::class);
    }

    public function testCreate(): void {
        $this->expectNotToPerformAssertions();
        RenderSkinHandler::create();
    }

    #[DataProvider('getResponseCases')]
    public function testHandleWithResponse(
        array $queryParams,
        int $expectedResponseStatus,
        string $expectedResponseBodyPath = null,
        int $skinResponseStatus = null,
        string $skinResponseBody = null,
    ): void {
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

    #[DataProvider('getExceptionCases')]
    public function testHandleWithException(
        array $queryParams,
        string $expectedException,
        string $expectedExceptionMessage = null,
        int $skinResponseStatus = null,
        string $skinResponseBody = null,
    ): void {
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

    public static function getResponseCases(): iterable {
        // Skins renders
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
        yield 'success response for slim skin' => [
            ['url' => 'http://ely.by/char.png', 'slim' => '1'],
            200,
            __DIR__ . '/../data/char-rendered-slim.png',
            200,
            file_get_contents(__DIR__ . '/../data/char-slim.png'),
        ];
        yield 'slim skin should be rendered as Steve if "slim" param not provided' => [
            ['url' => 'http://ely.by/char.png'],
            200,
            __DIR__ . '/../data/char-rendered-slim-steve.png',
            200,
            file_get_contents(__DIR__ . '/../data/char-slim.png'),
        ];
        // Face renders
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
        // Some expected errors
        yield 'url not allowed' => [
            ['url' => 'http://some-minecraft-resource.com/char.png'],
            403,
        ];
        yield 'url not allowed on allowed host' => [
            ['url' => 'http://skinsystem.ely.by/capes/char.png'],
            403,
        ];
        yield 'skin not found' => [
            ['url' => 'http://ely.by/char.png'],
            404,
            null,
            404,
        ];
    }

    public static function getExceptionCases(): iterable {
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
        yield 'request to render non 1.8 skin as slim' => [
            ['url' => 'http://ely.by/char.png', 'slim' => '1'],
            InvalidRequestException::class,
            'Cannot render skin with slim arms for non 1.8 skin format',
            200,
            file_get_contents(__DIR__ . '/../data/char.png'),
        ];
    }

}
