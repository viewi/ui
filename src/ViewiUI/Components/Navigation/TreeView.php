<?php

namespace Viewi\UI\Components\Navigation;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\Components\DOM\HtmlNode;

/**
 * A selectable tree, operable from the keyboard (WAI-ARIA tree):
 *
 *     <TreeView items="$nodes" selected="$openKey" label="Folders" (select)="open">
 *         <slotContent name="actions" data="$node"> …a ⋯ menu for $node… </slotContent>
 *         <slotContent name="edit" data="$node"> …an inline rename box… </slotContent>
 *     </TreeView>
 *
 * `items` is a flat list of TreeNode in tree order; `selected` is the key of the highlighted row.
 * Clicking a row, or Enter/Space on it, emits `select` with its key. One row is a tab stop (the
 * selected one), and inside the tree ArrowUp/ArrowDown move, Home/End jump, ArrowLeft goes to the
 * parent and ArrowRight to the first child.
 *
 * The `actions` slot renders beside every row's button, never inside it — a button inside a button
 * is not valid HTML. It shows on hover, on keyboard focus, and always on the selected row (touch screens
 * always). The `edit` slot replaces the button of the row whose key is `editingKey`.
 * `flat` drops the indentation (search results, whose parents may not be listed).
 */
class TreeView extends BaseComponent
{
    /** @var TreeNode[] */
    public array $items = [];
    public $selected = null;
    public $editingKey = null;
    public string $label = 'Tree';
    public bool $flat = false;
    /** Indentation per level, in rem. */
    public float $indent = 0.85;

    /** The row holding the tab stop; -1 = the selected row, or the first. */
    public int $focusIndex = -1;
    public ?HtmlNode $treeRoot = null;

    public function select(TreeNode $node, int $index)
    {
        $this->focusIndex = $index;
        $this->emitEvent('select', $node->key);
    }

    public function isSelected(TreeNode $node): bool
    {
        return $this->selected !== null && $node->key === $this->selected;
    }

    public function tabIndexOf(int $index): int
    {
        return $index === $this->tabStop() ? 0 : -1;
    }

    public function indentOf(TreeNode $node): string
    {
        $depth = $this->flat ? 1 : $node->depth;
        return 'padding-left: ' . (($depth - 1) * $this->indent) . 'rem';
    }

    public function levelOf(TreeNode $node): int
    {
        return $this->flat ? 1 : $node->depth;
    }

    /** Focus moved onto a row (click, Tab, arrows): it keeps the tab stop from now on. */
    public function onFocus(int $index)
    {
        $this->focusIndex = $index;
    }

    public function onKey(DomEvent $event)
    {
        // Only keys pressed ON a row: the arrows inside a row's ⋯ menu or its rename box belong to
        // them, and bubble up here too.
        <<<'javascript'
        if (!event.target || !event.target.getAttribute || event.target.getAttribute('role') !== 'treeitem') {
            return;
        }
        javascript;
        $key = $event->key;
        $size = count($this->items);
        if ($size === 0) {
            return;
        }
        $current = $this->tabStop();
        $next = -1;
        if ($key === 'ArrowDown') {
            $next = min($current + 1, $size - 1);
        } else if ($key === 'ArrowUp') {
            $next = max($current - 1, 0);
        } else if ($key === 'Home') {
            $next = 0;
        } else if ($key === 'End') {
            $next = $size - 1;
        } else if ($key === 'ArrowLeft' && !$this->flat) {
            $next = $this->parentIndex($current);
        } else if ($key === 'ArrowRight' && !$this->flat) {
            $next = $this->firstChildIndex($current);
        } else {
            return;
        }
        $event->preventDefault();
        if ($next >= 0) {
            $this->focusRow($next);
        }
    }

    /** The row that is Tab's way in: the one last focused, else the selected one, else the first. */
    private function tabStop(): int
    {
        $size = count($this->items);
        if ($this->focusIndex >= 0 && $this->focusIndex < $size) {
            return $this->focusIndex;
        }
        // Counted loops, not foreach-with-key: the transpiled foreach yields STRING keys in the
        // browser, and a string index never === the row's number.
        for ($position = 0; $position < $size; $position++) {
            if ($this->isSelected($this->items[$position])) {
                return $position;
            }
        }
        return 0;
    }

    private function parentIndex(int $index): int
    {
        $parentKey = $this->items[$index]->parentKey;
        if ($parentKey === null) {
            return -1;
        }
        $size = count($this->items);
        for ($position = 0; $position < $size; $position++) {
            if ($this->items[$position]->key === $parentKey) {
                return $position;
            }
        }
        return -1;
    }

    private function firstChildIndex(int $index): int
    {
        $next = $index + 1;
        if ($next < count($this->items) && $this->items[$next]->parentKey === $this->items[$index]->key
            && $this->items[$index]->key !== null) {
            return $next;
        }
        return -1;
    }

    private function focusRow(int $index)
    {
        $this->focusIndex = $index;
        <<<'javascript'
        const root = $this.treeRoot;
        if (root) {
            const rows = root.querySelectorAll('[role="treeitem"]');
            if (rows[index]) {
                rows[index].focus();
            }
        }
        javascript;
    }
}
