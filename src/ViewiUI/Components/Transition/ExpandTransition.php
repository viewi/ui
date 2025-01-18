<?php

namespace Viewi\UI\Components\Transition;

use Viewi\Builder\Attributes\ExtendWithJs;
use Viewi\Components\BaseComponent;

#[ExtendWithJs]
class ExpandTransition extends BaseComponent
{
    public bool $expanded = false;

    public function init()
    {
        $this->watch('expanded', function () {
            $this->onExpandedChange();
        });
    }

    public function onExpandedChange() {}

    public function rendered() {}
}
