<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Tests;

use Ely\SkinsRenderer\Application;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class ApplicationTest extends TestCase {

    /**
     * @var MockHandler
     */
    private $handler;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    protected function setUp() {
        $this->handler = new MockHandler();
        $handler = HandlerStack::create($this->handler);
        $client = new GuzzleClient(['handler' => $handler]);
        /** @var Application|\PHPUnit\Framework\MockObject\MockObject application */
        $this->application = $this->createPartialMock(Application::class, ['getClient']);
        $this->application->method('getClient')->willReturn($client);
        $this->request = $this->createMock(ServerRequestInterface::class);
    }

    /**
     * @dataProvider getCases
     */
    public function testHandle(
        string $requestUrl,
        array $queryParams,
        int $expectedResponseStatus,
        string $expectedResponseBodyPath = null,
        int $skinResponseStatus = null,
        string $skinResponseBody = null
    ) {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn($requestUrl);
        $this->request->method('getUri')->willReturn($uri);
        $this->request->method('getQueryParams')->willReturn($queryParams);
        if ($skinResponseStatus !== null) {
            $this->handler->append(new Response($skinResponseStatus, [], $skinResponseBody));
        }

        $response = $this->application->handle($this->request);
        $this->assertSame($expectedResponseStatus, $response->getStatusCode());
        if ($expectedResponseStatus === 200) {
            $this->assertSame('image/png', $response->getHeaderLine('Content-Type'));
            $content = $response->getBody()->getContents();
            $this->assertStringEqualsFile($expectedResponseBodyPath, $content);
        }
    }

    public function getCases() {
        yield 'success response' => [
            '/',
            ['url' => 'http://localhost/char.png'],
            200,
            __DIR__ . '/data/char-rendered.png',
            200,
            file_get_contents(__DIR__ . '/data/char.png'),
        ];
        yield 'success response scale@10' => [
            '/',
            ['url' => 'http://localhost/char.png', 'scale' => '10'],
            200,
            __DIR__ . '/data/char-rendered-scale-10.png',
            200,
            file_get_contents(__DIR__ . '/data/char.png'),
        ];
        yield 'success response with face render' => [
            '/',
            ['url' => 'http://localhost/char.png', 'renderFace' => '1'],
            200,
            __DIR__ . '/data/char-face-rendered.png',
            200,
            file_get_contents(__DIR__ . '/data/char.png'),
        ];
        yield 'success response with face render scale@10' => [
            '/',
            ['url' => 'http://localhost/char.png', 'renderFace' => '1', 'scale' => '10'],
            200,
            __DIR__ . '/data/char-face-rendered-scale-10.png',
            200,
            file_get_contents(__DIR__ . '/data/char.png'),
        ];
        yield 'unknown route' => [
            '/unknown-route',
            ['url' => 'http://localhost/char.png'],
            404,
        ];
        yield 'url not provided' => [
            '/',
            [],
            400,
        ];
        yield 'skin not found' => [
            '/',
            ['url' => 'http://localhost/char.png'],
            404,
            null,
            404,
        ];
        yield 'skin load server error' => [
            '/',
            ['url' => 'http://localhost/char.png'],
            500,
            null,
            503,
        ];
    }

}
