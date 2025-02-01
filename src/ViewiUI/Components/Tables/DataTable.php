<?php

namespace Viewi\UI\Components\Tables;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;

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

    public function __construct() {}

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
    }

    public function onDelete($item)
    {
        $this->emitEvent('delete', $item);
    }

    public function onPageChange()
    {
        $this->emitEvent('page', $this->filter->paging);
    }
}
