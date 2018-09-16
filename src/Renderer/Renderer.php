<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Renderer;

/**
 * Fix return types declarations.
 * @method static static assignSkinFromString(string $data)
 * @method static static assignSkinFromFile(string $filePath)
 */
class Renderer extends \ErickSkrauch\SkinRenderer2D\Renderer {

    public function setIsSlim(bool $isSlim): void {
        $this->isSlim = $isSlim;
    }

    public function isSlim(): bool {
        if ($this->isSlim === null) {
            $this->isSlim = $this->is1_8() && $this->checkOpacity(54, 20, true);
        }

        return $this->isSlim;
    }

}
