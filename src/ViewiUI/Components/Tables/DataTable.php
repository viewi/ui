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
    public bool $search = true;
    public bool $paging = true;
    public bool $add = true;
    public bool $remove = true;
    public bool $edit = true;
    public ?string $addLink = null;
    public ?string $addText = 'Add';
    public ?TableFilter $filter = null;

    public function __construct(
        #[Inject(Scope::PARENT)]
        private ?DataTableContext $tableContext
    ) {}

    public function init()
    {
        if ($this->tableContext !== null) {
            $this->tableContext->onUpdate(function (array $props) {
                // TODO: make Viewi feature, pass props though context
                $names = ['id', 'classList', 'items', 'columns', 'search', 'paging', 'add', 'remove', 'edit', 'filter', 'addText'];
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
            $this->filter = new TableFilter();
        }
    }

    public function onSearch(DomEvent $event)
    {
        $this->filter->searchText = $event->target->value;
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
        $this->emitEvent('edit', $item);
        $this->tableContext?->emitEvent('edit', $item);
    }

    public function onCreate()
    {
        $this->emitEvent('create');
        $this->tableContext?->emitEvent('create');
    }

    public function onPageChange()
    {
        $this->emitEvent('page', $this->filter->paging);
        $this->tableContext?->emitEvent('page', $this->filter->paging);
    }
}
