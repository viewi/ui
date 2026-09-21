<?php

namespace Viewi\UI\Components\Tables;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\Components\DOM\DomHelper;
use Viewi\Components\DOM\HtmlNode;
use Viewi\DI\Inject;
use Viewi\DI\Scope;

class DataTable extends BaseComponent
{
    public ?string $id = null;
    public ?string $classList = null;
    public array $items = [];
    public array $columns = [];
    public bool $search = false;
    public bool $paging = false;
    public bool $add = false;
    public bool $remove = false;
    public bool $edit = false;
    public ?string $addLink = null;
    public ?string $addText = 'Add';
    public bool $editInline = false;
    /**
     * On a phone (< 576px) lay each row out as a card instead of a sideways-scrolling
     * table (`.table-stacked` in ui.css). Cells flow inline with their column title as
     * a label; mark the cells that carry the row's identity with `table-stacked-main`
     * to lift them to the top at full width. Column templates receive the title as
     * `label` so they can render it as `data-label` too.
     */
    public bool $stacked = false;
    public ?TableFilter $filter = null;
    /**
     * The table's root element, from which the checkboxes are reached. Typed ?HtmlNode on purpose:
     * an untyped property is not wired to its `#ref`, and stays null.
     */
    public ?HtmlNode $tableRoot = null;
    public ?int $total = null;
    public ?int $pageSize = null;
    public $editItem = null;
    public bool $changeMode = false;
    /** Empty-state copy shown when there are no rows (see the 'empty' slot to override wholesale). */
    public string $emptyIcon = 'bi-inbox';
    public string $emptyTitle = 'Nothing here yet';
    public string $emptyText = '';
    /** Bound to the search box so "Clear search" can visibly reset it. */
    public string $searchValue = '';
    /**
     * Row selection for bulk actions: a checkbox per row, select-all in the header, and a bar over
     * the table (the `selection` slot) while anything is selected. Emits `selectionChange` with the
     * selected rows' keys.
     *
     * The selection is cleared whenever the rows change — another page, a new search, a different
     * filter — so an action can never apply to rows the person can no longer see.
     */
    public bool $selectable = false;
    /** What the selection bar says while nothing is selected; its space is kept either way. */
    public string $selectionHint = 'Select rows to act on several at once.';
    /** The property that identifies a row for selection. */
    public string $selectKey = 'Id';
    /**
     * Keys of the selected rows. Always replaced, never mutated in place: an array written into is
     * not reactive, so the checkboxes would not follow.
     */
    public array $selectedKeys = [];
    public bool $allSelected = false;
    public bool $someSelected = false;
    public int $selectedCount = 0;

    /**
     * "No rows" has two very different causes and must not share one message: the
     * list is genuinely empty (offer the create action), or the current search
     * filtered everything out (offer to clear it — offering "Add" there is wrong).
     */
    public function isFiltered(): bool
    {
        return $this->filter !== null && $this->filter->searchText !== '';
    }

    public function clearSearch()
    {
        $this->searchValue = '';
        $this->onSearch('');
    }

    public function __construct(
        #[Inject(Scope::PARENT)]
        private ?DataTableContext $tableContext
    ) {}

    public function init()
    {
        if ($this->tableContext !== null) {
            $this->tableContext->onUpdate(function (array $props) {
                // TODO: make Viewi feature, pass props though context
                // searchValue: a list restoring its search from the URL has to show it in the box, or
                // the rows are filtered by a term the person cannot see.
                $names = ['id', 'classList', 'items', 'columns', 'search', 'searchValue', 'paging', 'add', 'remove', 'edit', 'filter', 'addText', 'editInline', 'editItem', 'changeMode'];
                foreach ($names as $name) {
                    if (isset($props[$name])) {
                        $this->{$name} = $props[$name];
                    }
                }
                if (isset($props['items'])) {
                    // New rows (another page, a new search): drop the selection with them.
                    $this->clearSelection();
                }
            });
        }
    }

    public function isSelected($item): bool
    {
        return in_array($item->{$this->selectKey}, $this->selectedKeys, true);
    }

    public function toggleRow($item)
    {
        $key = $item->{$this->selectKey};
        if (in_array($key, $this->selectedKeys, true)) {
            $this->setSelection(array_values(array_filter($this->selectedKeys, fn($k) => $k !== $key)));
            return;
        }
        $this->setSelection([...$this->selectedKeys, $key]);
    }

    /** The header checkbox: select every row on the page, or — when all already are — none. */
    public function toggleAll()
    {
        if ($this->allSelected) {
            $this->setSelection([]);
            return;
        }
        $keys = [];
        foreach ($this->items as $item) {
            $keys[] = $item->{$this->selectKey};
        }
        $this->setSelection($keys);
    }

    public function clearSelection()
    {
        if (count($this->selectedKeys) > 0) {
            $this->setSelection([]);
        }
    }

    private function setSelection(array $keys)
    {
        $this->selectedKeys = $keys;
        $this->selectedCount = count($keys);
        $this->allSelected = count($this->items) > 0 && $this->selectedCount === count($this->items);
        $this->someSelected = $this->selectedCount > 0 && !$this->allSelected;
        // Set the checkboxes' DOM PROPERTIES, not only the bound `checked` attribute: once a person
        // has clicked a checkbox it stops following the attribute, so "select none" after ticking
        // two rows by hand left those two ticked. The header's half-checked state has no attribute
        // at all.
        // $tableRoot is null on the server, so this only ever runs in the browser.
        if ($this->tableRoot !== null) {
            $head = $this->tableRoot->querySelector('thead th.table-select input');
            if ($head !== null) {
                $head->checked = $this->allSelected;
                $head->indeterminate = $this->someSelected;
            }
            $boxes = DomHelper::getDomList($this->tableRoot->querySelectorAll('tbody td.table-select input'));
            $size = count($boxes);
            $rows = count($this->items);
            for ($i = 0; $i < $size; $i++) {
                $boxes[$i]->checked = $i < $rows
                    && in_array($this->items[$i]->{$this->selectKey}, $this->selectedKeys, true);
            }
        }
        $this->emitEvent('selectionChange', $keys);
    }

    public function mounted()
    {
        if ($this->filter === null) {
            $this->filter = new TableFilter(+ ($this->pageSize ?? 10));
            $this->filter->paging->setTotal(+ ($this->total ?? count($this->items)));
        }
    }

    public function finishEdit()
    {
        $this->changeMode = false;
        $this->editItem = null;
    }

    public function onSearch(string $content)
    {
        $this->filter->searchText = $content;
        $this->filter->paging->page = 1;
        $this->emitEvent('search', $this->filter->searchText);
        $this->tableContext?->emitEvent('search', $this->filter->searchText);
    }

    public function onDelete($item)
    {
        $this->emitEvent('delete', $item);
        $this->tableContext?->emitEvent('delete', $item);
    }

    public function onEdit($item)
    {
        if ($this->editInline) {
            $this->editItem = $item;
            $this->changeMode = true;
        }
        $this->emitEvent('edit', $item);
        $this->tableContext?->emitEvent('edit', $item);
    }

    public function onCreate()
    {
        $this->emitEvent('create');
        $this->tableContext?->emitEvent('create');
    }

    public function onSave($item)
    {
        $this->emitEvent('save', $item);
        $this->tableContext?->emitEvent('save', $item);
    }

    public function onCancel()
    {
        $editedItem = $this->editItem;
        $this->editItem = null;
        $this->changeMode = false;
        $this->emitEvent('cancel', $editedItem);
        $this->tableContext?->emitEvent('cancel', $editedItem);
    }

    public function onPageChange()
    {
        $this->emitEvent('page', $this->filter->paging);
        $this->tableContext?->emitEvent('page', $this->filter->paging);
    }
}
