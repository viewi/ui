<?php

namespace Viewi\UI\Components\Tables;

class TableColumn
{
    /**
     * @param ?string $cssClass Classes for the column's header cell — e.g. 'd-none d-xl-table-cell' to
     *                       drop a column where the table is narrow. DataTable only owns the <th>;
     *                       a slot-rendered column must put the same classes on its own <td>.
     *                       Not `$class`: the name reaches the transpiled JS verbatim, where
     *                       `class` is a reserved word and breaks the whole bundle.
     */
    public function __construct(public string $key, public ?string $title = null, public ?string $template = null, public ?string $cssClass = null) {}
}
