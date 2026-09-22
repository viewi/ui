<?php

namespace Viewi\UI\Components\Navigation;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomHelper;
use Viewi\Components\DOM\HtmlNode;
use Viewi\Components\Environment\ClientTimer;
// Same namespace, but the bundler only ships classes a component imports: without this `use`
// the browser threw "TreeSearch is not defined" on the first keystroke (SSR never noticed).
use Viewi\UI\Components\Navigation\TreeNode;
use Viewi\UI\Components\Navigation\TreeSearch;

/**
 * Pick one node of a tree: a button that opens a search box above a collapsible TreeView.
 *
 *     <TreePicker items="$nodes" selected="$parentId" label="Move into" (select)="onPick" />
 *     <TreePicker items="$nodes" text="Move to..." icon="bi-folder-symlink" disabledKeys="$here" (select)="moveTo" />
 *
 * `items` are TreeNode in tree order; give each a `title` holding its full path ("Campaigns / Q4"):
 * the search matches it (TreeSearch: "q4", "campaigns/q4", "/campaigns") and lists matches flat by
 * path. `disabledKeys` are shown but cannot be chosen. The button reads `text` when set, else the
 * selected node's path, else `placeholder`. Choosing emits `select` with the key and closes;
 * Escape or a click outside closes without choosing.
 */
class TreePicker extends BaseComponent
{
    /** @var TreeNode[] */
    public array $items = [];
    public $selected = null;
    public array $disabledKeys = [];
    /** Accessible name, and what the popup is for. */
    public string $label = 'Choose';
    public string $text = '';
    public string $placeholder = 'Choose...';
    public string $icon = '';
    public string $buttonClass = 'btn btn-outline-secondary btn-sm';
    public string $searchPlaceholder = 'Find...';
    public string $align = 'start';
    public int $collapseOver = 20;

    public bool $isOpen = false;
    public string $term = '';
    public bool $searching = false;
    public bool $noMatches = false;
    /**
     * What the tree shows: `items`, or the matches listed flat by path while searching. Match nodes
     * are cached per key, so typing keeps their elements (see TreeView::$rows).
     * @var TreeNode[]
     */
    public array $visible = [];
    private array $flatCache = /* @jsobject */ [];
    public ?HtmlNode $trigger = null;
    public ?HtmlNode $panel = null;

    public function init()
    {
        $this->watch('term', fn() => $this->filter());
        $this->watch('items', fn() => $this->filter());
    }

    public function mounted()
    {
        $this->filter();
    }

    public function buttonText(): string
    {
        if ($this->text !== '') {
            return $this->text;
        }
        foreach ($this->items as $node) {
            if ($this->selected !== null && $node->key === $this->selected) {
                return $node->title !== '' ? $node->title : $node->label;
            }
        }
        return $this->placeholder;
    }

    public function toggle()
    {
        if ($this->isOpen) {
            $this->close();
            return;
        }
        $this->term = '';
        $this->filter();
        $this->isOpen = true;
        // The panel renders on the next tick, inside an Overlay that measures itself first.
        ClientTimer::setTimeoutStatic(fn() => $this->focusSearch(10), 0);
    }

    public function close()
    {
        $this->isOpen = false;
    }

    /** Escape: close and hand focus back to the button, as a keyboard user expects. */
    public function cancel()
    {
        $this->isOpen = false;
        if ($this->trigger !== null) {
            $this->trigger->focus();
        }
    }

    public function choose($key)
    {
        if (in_array($key, $this->disabledKeys, true)) {
            return;
        }
        $this->isOpen = false;
        if ($this->trigger !== null) {
            $this->trigger->focus();
        }
        $this->emitEvent('select', $key);
    }

    public function filter()
    {
        $this->searching = trim($this->term) !== '';
        if (!$this->searching) {
            $this->visible = $this->items;
            $this->noMatches = false;
            return;
        }
        $matches = [];
        foreach ($this->items as $node) {
            $path = $node->title !== '' ? $node->title : $node->label;
            if (TreeSearch::matches($path, $node->label, $this->term)) {
                $matches[] = $this->flatNode($node, $path);
            }
        }
        $this->visible = $matches;
        $this->noMatches = count($matches) === 0;
    }

    /** The node as a flat search result: its path as the label, one level, no parent. */
    private function flatNode(TreeNode $node, string $path): TreeNode
    {
        $cached = $this->flatCache[$node->key] ?? null;
        if ($cached !== null && $cached->label === $path && $cached->badge === $node->badge) {
            return $cached;
        }
        $flat = new TreeNode($node->key, $path, 1, null, $node->icon, $node->badge, $path);
        $this->flatCache[$node->key] = $flat;
        return $flat;
    }

    public function focusSearch(int $tries)
    {
        if ($this->panel === null) {
            if ($tries > 0) {
                DomHelper::requestAnimationFrame(fn() => $this->focusSearch($tries - 1));
            }
            return;
        }
        $box = $this->panel->querySelector('input');
        if ($box !== null) {
            $box->focus();
        }
        if (($box === null || DomHelper::getActiveElement() !== $box) && $tries > 0) {
            DomHelper::requestAnimationFrame(fn() => $this->focusSearch($tries - 1));
        }
    }
}
