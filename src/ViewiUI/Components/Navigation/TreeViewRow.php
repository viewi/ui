<?php

namespace Viewi\UI\Components\Navigation;

/**
 * One visible row of a TreeView: the consumer's node and whether it has children. Whether it is
 * open is asked of the tree (isOpen), not stored here: rows are reused across rebuilds, so a
 * stored flag would go stale on the very object the template keeps.
 */
class TreeViewRow
{
    public function __construct(
        public TreeNode $node,
        public bool $hasChildren = false
    ) {
    }
}
