<?php

namespace Viewi\UI\Components\Navigation;

/**
 * One row of a TreeView. The list is flat and already in tree order (a parent, then its children);
 * `depth` (1 = top level) indents it and `parentKey` is where ArrowLeft goes.
 */
class TreeNode
{
    public function __construct(
        public $key,
        public string $label,
        public int $depth = 1,
        public $parentKey = null,
        public string $icon = '',
        public string $badge = '',
        public string $title = ''
    ) {
    }
}
