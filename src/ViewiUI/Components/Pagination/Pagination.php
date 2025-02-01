<?php

namespace Viewi\UI\Components\Pagination;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;

class Pagination extends BaseComponent
{
    public ?PaginationModel $paging = null;

    public function mounted()
    {
        if ($this->paging === null) {
            $this->paging = new PaginationModel(1, 10, 1);
        }
    }

    public function setPage(DomEvent $event, int $page)
    {
        $event->preventDefault();
        if (is_numeric($page) && $page !== $this->paging->page) {
            $this->paging->setPage($page);
            $this->emitEvent('change', $this->paging);
        } else {
            $event->target->blur();
        }
    }
}
