<?php

namespace Viewi\UI\Components\Tabs;

class TabItem
{
    public function __construct(public string $title, public bool $active = false) {}
}
