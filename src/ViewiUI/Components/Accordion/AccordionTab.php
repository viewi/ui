<?php

namespace Viewi\UI\Components\Accordion;

use Viewi\Components\BaseComponent;

class AccordionTab extends BaseComponent
{
    public ?string $id = null;
    public ?string $classList = null;
    public string $title = '';
    public bool $open = false;

    public function toggle()
    {
        $this->open = !$this->open;
    }
}
