<?php

namespace Viewi\UI\Components\Tables;

class TableColumn
{
    public function __construct(public string $key, public ?string $type = null, public ?string $title = null) {}

    const TYPE_DATETIME = 'datetime';
    const TYPE_BOOLEAN = 'boolean';
}
