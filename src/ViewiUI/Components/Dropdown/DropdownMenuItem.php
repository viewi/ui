<?php

namespace Viewi\UI\Components\Dropdown;

/**
 * One entry in a DropdownMenu. Named DropdownMenuItem, not MenuItem: Viewi class names are global in
 * the browser bundle, and an app's own navigation MenuItem would silently become this class.
 *
 * Plain data: the menu reports which `key` was chosen and the page
 * decides what that means, so nothing here has to carry a callback.
 */
class DropdownMenuItem
{
    public function __construct(
        public string $key,
        public string $label,
        public string $icon = '',
        /** Destructive (delete, remove): rendered in the danger colour, so it reads before it is pressed. */
        public bool $danger = false,
        public bool $disabled = false
    ) {
    }
}
