<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Tests\Exceptions;

use Ely\SkinsRenderer\Exceptions\UnknownUrlException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ely\SkinsRenderer\Exceptions\UnknownUrlException
 */
class UnknownUrlExceptionTest extends TestCase {

    public function testException() {
        $exception = new UnknownUrlException('/find-me');
        $this->assertSame('Unknown url', $exception->getMessage());
        $this->assertSame('/find-me', $exception->getUrl());
    }

}
