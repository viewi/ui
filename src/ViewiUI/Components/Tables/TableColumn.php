<?php

namespace Viewi\UI\Components\Tables;

class TableColumn
{
    public function __construct(public string $key, public ?string $title = null, public ?string $template = null) {}
}
