<?php

namespace Viewi\UI\Components\Tables;

use Viewi\UI\Components\Pagination\PaginationModel;

class TableFilter
{
    public string $searchText = '';
    public PaginationModel $paging;

    public function __construct(int $pageSize = 10)
    {
        $this->paging = new PaginationModel(1, $pageSize, 0);
    }
}
