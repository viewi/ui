<?php

namespace Viewi\UI\Components\Pagination;

class PaginationModel
{
    public int $totalPages = 1;
    public array $pages = [];
    public int $entityFrom = 1;
    public int $entityTo = 10;

    public function __construct(public int $page, public int $size, public int $total = 0, public int $totalVisible = 9)
    {
        $this->calculatePages();
    }

    public function setTotal(int $total)
    {
        $this->total = $total;
        $this->calculateInfo();
        $this->calculatePages();
    }

    public function setPage(int $page)
    {
        $this->page = $page;
        $this->calculateInfo();
    }

    public function setPageSize(int $size)
    {
        $this->size = $size;
        $this->calculateInfo();
        $this->calculatePages();
    }

    // Showing {($paging->page - 1) * $paging->size + 1} to {min($paging->page * $paging->size, $paging->total)} of {$paging->total} entries

    public function calculateInfo()
    {
        $this->entityFrom = ($this->page - 1) * $this->size + 1;
        $this->entityTo = min($this->page * $this->size, $this->total);
    }

    public function calculatePages()
    {
        $this->totalPages = ceil($this->total / $this->size);
        $pages = [];
        $startFrom = 1;
        $endTill = $this->totalPages;
        // total cases: 6
        // [1,2,3,4,5,6,7] - 1

        // [1,2,3,4,5,6,7,8]
        // [1,2,3,4,5,-,8] - 2 page <=4
        // [1,-,4,5,6,7,8] - 3 page >=total -3

        // [1,2,3,4,5,6,7,8,9]
        // [1,2,3,4,5,-,9] - 4 page <=4
        // [1,-,5,6,7,8,9] - 5 page >=total -3
        // [1,-,4,5,6,-,9] - 6 page 4 <= x <= total -4
        $hasEnding = false;
        if ($this->totalPages > $this->totalVisible) { // case > 1
            $edge = $this->totalVisible - 3; // ceil(($this->totalVisible + 1) / 2);
            if ($this->page <= $edge) { // case 2 and 4
                $endTill = $edge + 1;
                $hasEnding = true;
            } elseif ($this->page >= $this->totalPages - $edge + 1) { // case 3,5
                $pages[] = 1;
                $pages[] = '...';
                $startFrom = $this->totalPages - $edge;
            } else { // case 6
                $pages[] = 1;
                $pages[] = '...';
                $border = ceil(($this->totalVisible - 5) / 2);
                $startFrom = $this->page - $border;
                $endTill = $this->page + $border;
                $hasEnding = true;
            }
        }
        for ($i = $startFrom; $i <= $endTill; $i++) {
            $pages[] = $i;
        }
        if ($hasEnding) {
            $pages[] = '...';
            $pages[] = $this->totalPages;
        }

        $this->pages = $pages;
    }
}
