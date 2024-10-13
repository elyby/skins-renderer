<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Tests;

use AspectMock\Test;
use Ely\SkinsRenderer\Application;
use Ely\SkinsRenderer\Exceptions\InvalidRequestException;
use Ely\SkinsRenderer\Exceptions\UnknownUrlException;
use Ely\SkinsRenderer\Handlers\HandlerFactory;
use Ely\SkinsRenderer\Handlers\HandlerInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

/**
 * @covers \Ely\SkinsRenderer\Application
 */
final class ApplicationTest extends TestCase {

    protected function tearDown(): void {
        Test::clean();
    }

    public function testHandle(): void {
        $expectedResponse = new Response(200, [], 'find me');
        $handler = $this->createMock(HandlerInterface::class);
        $handler->method('handle')->willReturn($expectedResponse);
        Test::double(HandlerFactory::class, ['createFromRequest' => $handler]);
        $application = new Application();
        /** @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $application->handle($request);
        $this->assertSame($expectedResponse, $response);
    }

    /**
     * @dataProvider getExceptionsMap
     */
    public function testHandleException(
        Exception $thrownException,
        int $expectedStatusCode,
        string $expectedMessageInDebugMode
    ): void {
        Test::double(HandlerFactory::class, ['createFromRequest' => function() use ($thrownException) {
            throw $thrownException;
        }]);
        $application = new Application();
        /** @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $application->handle($request);
        $this->assertSame($expectedStatusCode, $response->getStatusCode());
        $this->assertSame('', (string)$response->getBody());

        $application->setDebug(true);
        $response = $application->handle($request);
        $this->assertSame($expectedStatusCode, $response->getStatusCode());
        $this->assertSame($expectedMessageInDebugMode, (string)$response->getBody());
    }

    public function getExceptionsMap(): iterable {
        yield [new UnknownUrlException('/test'), 404, 'Unknown url'];
        yield [new InvalidRequestException('Find me'), 400, 'Find me'];
        yield [new Exception('Some shit happened'), 500, 'Some shit happened'];
    }

    public function testSetEnvironment(): void {
        $application = new Application();
        $this->assertSame('prod', $application->getEnvironment());
        $application->setEnvironment('dev');
        $this->assertSame('dev', $application->getEnvironment());
    }

    public function testSetDebug(): void {
        $application = new Application();
        $this->assertFalse($application->isDebug());
        $application->setDebug(true);
        $this->assertTrue($application->isDebug());
    }

}
