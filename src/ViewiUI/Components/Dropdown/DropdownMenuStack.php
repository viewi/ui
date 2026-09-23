<?php

namespace Viewi\UI\Components\Dropdown;

use Viewi\DI\Singleton;

/**
 * At most one DropdownMenu open at a time, across the page.
 *
 * A menu's trigger stops its click from bubbling (so a ⋯ inside a clickable row does not also
 * select the row), which also means the click never reaches the document - so the menu that was
 * ALREADY open never hears it as a click outside, and opening menus one after another stacked
 * them. Each menu reports here when it opens; the one it replaces closes.
 */
#[Singleton]
class DropdownMenuStack
{
    public ?DropdownMenu $open = null;

    /** $menu has just opened: close whichever other menu was open. */
    public function opened(DropdownMenu $menu)
    {
        $previous = $this->open;
        $this->open = $menu;
        if ($previous !== null && $previous !== $menu) {
            $previous->close(false);
        }
    }

    /** $menu has closed: forget it, unless another one has taken its place already. */
    public function closed(DropdownMenu $menu)
    {
        if ($this->open === $menu) {
            $this->open = null;
        }
    }
}
