<?php

namespace Viewi\UI\Components\Dropdown;

/**
 * One option as SelectInput renders it: the items it was given, normalised once, so the template,
 * the keyboard cursor and the aria ids all work from a position — whatever shape the items came in
 * (plain values, objects with itemTitle/itemValue, or an associative map). Named for the component:
 * Viewi class names are global in the browser bundle.
 */
class SelectInputOption
{
    /** 0-based place in the list: the keyboard cursor and the option's element id. */
    public int $position = 0;
    /** What selecting it hands to selectItem(): the item itself, or the key of an associative map. */
    public $raw = null;
    public string $title = '';
}
