<?php

namespace Viewi\UI\Components\Tables;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
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
                $names = ['id', 'classList', 'items', 'columns', 'search', 'paging', 'add', 'remove', 'edit', 'filter', 'addText', 'editInline', 'editItem', 'changeMode'];
                foreach ($names as $name) {
                    if (isset($props[$name])) {
                        $this->{$name} = $props[$name];
                    }
                }
            });
        }
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
