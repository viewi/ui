<?php

namespace Viewi\UI\Components\Navigation;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\Components\DOM\DomHelper;
use Viewi\Components\DOM\HtmlNode;
use Viewi\UI\Components\Navigation\TreeViewRow;

/**
 * A selectable, collapsible tree, operable from the keyboard (WAI-ARIA tree):
 *
 *     <TreeView items="$nodes" selected="$openKey" label="Folders" (select)="open">
 *         <slotContent name="actions" data="$node"> ...a menu for $node... </slotContent>
 *         <slotContent name="edit" data="$node"> ...an inline rename box... </slotContent>
 *     </TreeView>
 *
 * `items` is a flat list of TreeNode in tree order (a parent, then its children); `selected` is the
 * key of the highlighted row. Clicking a row, or Enter/Space on it, emits `select` with its key.
 *
 * A node with children has a chevron. A small tree (`collapseOver` nodes or fewer) starts fully
 * expanded; a larger one starts collapsed except the path to the selected node. Once someone
 * expands or collapses by hand, their choice is kept when the items reload. Selecting a node always
 * expands the path to it, so the highlight is never hidden inside a closed branch.
 *
 * Keyboard: one row is a Tab stop (the selected one). ArrowUp/ArrowDown move between visible rows,
 * Home/End jump, ArrowRight expands a closed node or moves to its first child, ArrowLeft collapses
 * an open node or moves to its parent.
 *
 * The `actions` slot renders beside every row's button, never inside it: a button inside a button
 * is not valid HTML. It shows on hover, on keyboard focus, and always on the selected row (touch
 * screens always). The `edit` slot replaces the button of the row whose key is `editingKey`.
 * `flat` lists every item without indentation or chevrons (search results, whose parents may not
 * be listed).
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
    /** Up to this many nodes the tree starts fully expanded; more, and it starts collapsed. 0 = never. */
    public int $collapseOver = 20;
    /** Keys shown but not choosable (e.g. where things already are); they never emit `select`. */
    public array $disabledKeys = [];
    /**
     * Drag and drop. Rows whose key is in `draggableKeys` can be picked up (`dragStart` with the
     * key, `dragEnd` however it ends). While `dropEnabled`, every row but `dropDisabledKeys` accepts
     * a drop: it highlights under the pointer and emits `drop` with its key. A disabled row never
     * accepts, so the browser shows "no drop" there and nothing is emitted. What is being dragged
     * - a row of this tree or something from elsewhere on the page - is the page's business.
     */
    public array $draggableKeys = [];
    public bool $dropEnabled = false;
    public array $dropDisabledKeys = [];
    /** The row under the pointer during a drag, null = none. */
    public $dropKey = null;

    /**
     * What the template draws: the visible rows. A PROPERTY rebuilt on every change, because a
     * foreach over a method never re-renders and an `if=` cannot call a method with arguments.
     *
     * The row OBJECTS are reused across rebuilds (see $rowCache): Viewi's foreach keeps an element
     * only when its item is the same object, so fresh objects re-created every button, and the one
     * with keyboard focus lost it on each expand or collapse. For the same reason the template's
     * foreach has no `$index =>`: a keyed foreach also requires the same key, and a collapse shifts
     * every index below it.
     * @var TreeViewRow[]
     */
    public array $rows = [];
    /** node key => its row, so an unchanged node keeps the same row object. */
    private array $rowCache = /* @jsobject */ [];
    /** Keys of the open nodes. */
    public array $expandedKeys = [];
    /** Set once someone expands or collapses by hand: from then on a reload keeps their choice. */
    public bool $touched = false;

    /** The row holding the tab stop; -1 = the selected row, or the first. */
    public int $focusIndex = -1;
    public ?HtmlNode $treeRoot = null;

    public function init()
    {
        $this->watch('items', fn() => $this->onItems());
        $this->watch('selected', fn() => $this->onSelected());
        $this->watch('flat', fn() => $this->buildRows());
    }

    /**
     * The first build. Not in mounting(): Viewi assigns props AFTER mounting() and before mounted(),
     * on the server and in the browser alike, so only here are the items there to build from. The
     * watchers above handle every later change.
     */
    public function mounted()
    {
        $this->onItems();
    }

    public function onItems()
    {
        if (!$this->touched) {
            $this->expandedKeys = $this->defaultExpanded();
        }
        $this->reveal();
        $this->buildRows();
    }

    public function onSelected()
    {
        $this->reveal();
        $this->buildRows();
    }

    public function select(TreeViewRow $row)
    {
        $this->focusIndex = $this->positionOf($row);
        if ($this->isDisabled($row)) {
            return;
        }
        $this->emitEvent('select', $row->node->key);
    }

    public function isDisabled(TreeViewRow $row): bool
    {
        return in_array($row->node->key, $this->disabledKeys, true);
    }

    public function isSelected(TreeNode $node): bool
    {
        return $this->selected !== null && $node->key === $this->selected;
    }

    public function tabIndexOf(TreeViewRow $row): int
    {
        return $this->positionOf($row) === $this->tabStop() ? 0 : -1;
    }

    public function isOpen(TreeViewRow $row): bool
    {
        return $row->hasChildren && in_array($row->node->key, $this->expandedKeys, true);
    }

    /** Where a row sits among the visible rows; -1 if it is not one of them. */
    private function positionOf(TreeViewRow $row): int
    {
        $size = count($this->rows);
        for ($position = 0; $position < $size; $position++) {
            if ($this->rows[$position] === $row) {
                return $position;
            }
        }
        return -1;
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

    /** aria-expanded for a node with children; null (no attribute) for a leaf. */
    public function expandedAttr(TreeViewRow $row): ?string
    {
        if (!$row->hasChildren) {
            return null;
        }
        return $this->isOpen($row) ? 'true' : 'false';
    }

    public function isDraggable(TreeViewRow $row): bool
    {
        return in_array($row->node->key, $this->draggableKeys, true);
    }

    public function canDrop(TreeViewRow $row): bool
    {
        return $this->dropEnabled && !in_array($row->node->key, $this->dropDisabledKeys, true);
    }

    public function isDropTarget(TreeViewRow $row): bool
    {
        return $this->dropKey !== null && $row->node->key === $this->dropKey;
    }

    public function onDragStart(TreeViewRow $row, DomEvent $event)
    {
        if (!$this->isDraggable($row)) {
            return;
        }
        // Firefox starts no drag without data.
        if ($event->dataTransfer !== null) {
            $event->dataTransfer->setData('text/plain', '' . $row->node->key);
            $event->dataTransfer->effectAllowed = 'move';
        }
        $this->emitEvent('dragStart', $row->node->key);
    }

    public function onDragEnd()
    {
        $this->dropKey = null;
        $this->emitEvent('dragEnd', true);
    }

    /** Accepting a drop means cancelling dragover; a row that will not take it simply does not. */
    public function onDragOver(TreeViewRow $row, DomEvent $event)
    {
        if (!$this->canDrop($row)) {
            return;
        }
        $event->preventDefault();
        if ($event->dataTransfer !== null) {
            $event->dataTransfer->dropEffect = 'move';
        }
        if ($this->dropKey !== $row->node->key) {
            $this->dropKey = $row->node->key;
        }
    }

    public function onDragLeave(TreeViewRow $row)
    {
        if ($this->dropKey === $row->node->key) {
            $this->dropKey = null;
        }
    }

    public function onDrop(TreeViewRow $row, DomEvent $event)
    {
        $event->preventDefault();
        $this->dropKey = null;
        if ($this->canDrop($row)) {
            $this->emitEvent('drop', $row->node->key);
        }
    }

    /** The chevron: open or close a node without selecting it. */
    public function toggle(TreeViewRow $row)
    {
        $this->setExpanded($row->node->key, !$this->isOpen($row));
    }

    /** Focus moved onto a row (click, Tab, arrows): it keeps the tab stop from now on. */
    public function onFocus(TreeViewRow $row)
    {
        $this->focusIndex = $this->positionOf($row);
    }

    public function onKey(DomEvent $event)
    {
        // Only keys pressed ON a row: the arrows inside a row's menu or its rename box belong to
        // them, and bubble up here too.
        if ($event->target->getAttribute('role') !== 'treeitem') {
            return;
        }
        $key = $event->key;
        $size = count($this->rows);
        if ($size === 0) {
            return;
        }
        $current = $this->tabStop();
        $row = $this->rows[$current];
        $next = -1;
        if ($key === 'ArrowDown') {
            $next = min($current + 1, $size - 1);
        } else if ($key === 'ArrowUp') {
            $next = max($current - 1, 0);
        } else if ($key === 'Home') {
            $next = 0;
        } else if ($key === 'End') {
            $next = $size - 1;
        } else if ($key === 'ArrowRight' && !$this->flat) {
            if ($row->hasChildren && !$this->isOpen($row)) {
                $event->preventDefault();
                $this->setExpanded($row->node->key, true);
                return;
            }
            $next = $row->hasChildren ? $current + 1 : -1;
        } else if ($key === 'ArrowLeft' && !$this->flat) {
            if ($row->hasChildren && $this->isOpen($row)) {
                $event->preventDefault();
                $this->setExpanded($row->node->key, false);
                return;
            }
            $next = $this->parentIndex($current);
        } else {
            return;
        }
        $event->preventDefault();
        if ($next >= 0) {
            $this->focusRow($next);
        }
    }

    // --- state -------------------------------------------------------------------------------

    private function setExpanded($key, bool $open)
    {
        $this->touched = true;
        $keys = [];
        foreach ($this->expandedKeys as $expanded) {
            if ($expanded !== $key) {
                $keys[] = $expanded;
            }
        }
        if ($open) {
            $keys[] = $key;
        }
        $this->expandedKeys = $keys;
        $this->buildRows();
    }

    /** Every node with children when the tree is small; otherwise none (reveal() then opens a path). */
    private function defaultExpanded(): array
    {
        $size = count($this->items);
        if ($this->collapseOver > 0 && $size > $this->collapseOver) {
            return [];
        }
        $keys = [];
        for ($i = 0; $i < $size; $i++) {
            if ($this->hasChildrenAt($i)) {
                $keys[] = $this->items[$i]->key;
            }
        }
        return $keys;
    }

    /** Open every ancestor of the selected node, so the highlight is never inside a closed branch. */
    private function reveal()
    {
        if ($this->selected === null) {
            return;
        }
        $parents = $this->parentsByKey();
        $keys = $this->expandedKeys;
        $node = $this->nodeByKey($this->selected);
        $guard = 0;
        while ($node !== null && $node->parentKey !== null && $guard < 64) {
            if (!in_array($node->parentKey, $keys, true)) {
                $keys[] = $node->parentKey;
            }
            $node = $parents[$node->parentKey] ?? null;
            $guard++;
        }
        $this->expandedKeys = $keys;
    }

    /** The visible rows: every item while flat; otherwise those whose ancestors are all open. */
    private function buildRows()
    {
        $rows = [];
        $size = count($this->items);
        // Depth of the closed node whose branch is being skipped; 0 = not skipping. Items come in
        // tree order, so a branch ends at the first item that is not deeper than its root.
        $skipBelow = 0;
        for ($i = 0; $i < $size; $i++) {
            $node = $this->items[$i];
            if ($skipBelow > 0) {
                if ($node->depth > $skipBelow) {
                    continue;
                }
                $skipBelow = 0;
            }
            $hasChildren = !$this->flat && $this->hasChildrenAt($i);
            $row = $this->rowCache[$node->key] ?? null;
            if ($row === null || $row->node !== $node || $row->hasChildren !== $hasChildren) {
                $row = new TreeViewRow($node, $hasChildren);
                $this->rowCache[$node->key] = $row;
            }
            $rows[] = $row;
            if ($hasChildren && !in_array($node->key, $this->expandedKeys, true)) {
                $skipBelow = $node->depth;
            }
        }
        $this->rows = $rows;
        if ($this->focusIndex >= count($rows)) {
            $this->focusIndex = -1;
        }
    }

    private function hasChildrenAt(int $index): bool
    {
        $next = $index + 1;
        return $next < count($this->items) && $this->items[$index]->key !== null
            && $this->items[$next]->parentKey === $this->items[$index]->key;
    }

    private function nodeByKey($key): ?TreeNode
    {
        foreach ($this->items as $node) {
            if ($node->key === $key) {
                return $node;
            }
        }
        return null;
    }

    /** key => node, for walking up the tree. */
    private function parentsByKey(): array
    {
        $map = /* @jsobject */ [];
        foreach ($this->items as $node) {
            $map[$node->key] = $node;
        }
        return $map;
    }

    // --- keyboard ----------------------------------------------------------------------------

    /** The row that is Tab's way in: the one last focused, else the selected one, else the first. */
    private function tabStop(): int
    {
        $size = count($this->rows);
        if ($this->focusIndex >= 0 && $this->focusIndex < $size) {
            return $this->focusIndex;
        }
        // Counted loops, not foreach-with-key: the transpiled foreach yields STRING keys in the
        // browser, and a string index never === the row's number.
        for ($position = 0; $position < $size; $position++) {
            if ($this->isSelected($this->rows[$position]->node)) {
                return $position;
            }
        }
        return 0;
    }

    private function parentIndex(int $index): int
    {
        $parentKey = $this->rows[$index]->node->parentKey;
        if ($parentKey === null) {
            return -1;
        }
        $size = count($this->rows);
        for ($position = 0; $position < $size; $position++) {
            if ($this->rows[$position]->node->key === $parentKey) {
                return $position;
            }
        }
        return -1;
    }

    private function focusRow(int $index)
    {
        $this->focusIndex = $index;
        if ($this->treeRoot === null) {
            return;
        }
        $rows = DomHelper::getDomList($this->treeRoot->querySelectorAll('[role="treeitem"]'));
        if ($index < count($rows)) {
            $rows[$index]->focus();
        }
    }
}
