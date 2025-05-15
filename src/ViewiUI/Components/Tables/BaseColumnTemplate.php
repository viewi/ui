<?php

namespace Viewi\UI\Components\Tables;

use Viewi\Components\BaseComponent;

abstract class BaseColumnTemplate extends BaseComponent
{
    public $value = null;
    public $data = null;

    public function getCurrentValue()
    {
        return $this->value;
    }

    public function getCurrentItem()
    {
        return $this->data;
    }
}
