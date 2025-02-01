<?php

namespace Viewi\UI\Components\Overlay;

use Viewi\DI\Singleton;

#[Singleton]
class OverlayStack
{
    private int $zIndex = 1110;
    private int $increment = 10;
    private array $stack = [];

    public function getZIndex()
    {
        $total = count($this->stack);
        if ($total > 0) {
            $nextIndex = $this->stack[$total - 1] + $this->increment;
        } else {
            $nextIndex = $this->zIndex;
        }
        $this->stack[] = $nextIndex;
        return $nextIndex;
    }

    public function put(int $zIndex)
    {
        $index = array_search($zIndex, $this->stack);
        if ($index !== false) {
            array_splice($this->stack, $index, 1);
        }
    }

    public function setZIndex(int $zIndex)
    {
        $this->zIndex = $zIndex;
    }

    public function setIncrement(int $increment)
    {
        $this->increment = $increment;
    }
}
