<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Tests\Renderer;

use Ely\SkinsRenderer\Renderer\Renderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ely\SkinsRenderer\Renderer\Renderer
 */
class RendererTest extends TestCase {

    /**
     * @covers \Ely\SkinsRenderer\Renderer\Renderer::setIsSlim
     * @covers \Ely\SkinsRenderer\Renderer\Renderer::isSlim
     */
    public function testSetIsSlim() {
        $renderer = Renderer::assignSkinFromFile(__DIR__ . '/../data/char-slim.png');
        $this->assertTrue($renderer->isSlim(), 'skin is slim and library should automatically detect it');

        $renderer = Renderer::assignSkinFromFile(__DIR__ . '/../data/char-slim.png');
        $renderer->setIsSlim(false);
        $this->assertFalse($renderer->isSlim(), 'provided manually value is expected');
    }

}
