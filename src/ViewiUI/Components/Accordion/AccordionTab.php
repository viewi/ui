<?php

namespace Viewi\UI\Components\Accordion;

use Viewi\Components\BaseComponent;
use Viewi\DI\Inject;
use Viewi\DI\Scope;

class AccordionTab extends BaseComponent
{
    public ?string $id = null;
    public ?string $classList = null;
    public string $title = '';
    public bool $open = false;

    public function __construct(
        #[Inject(Scope::PARENT)]
        private ?AccordionContext $context
    ) {}

    public function init()
    {
        $this->context?->add($this);
    }

    public function toggle()
    {
        if ($this->context) {
            $this->context->collapse($this);
        } else {
            $this->open = !$this->open;
        }
    }
}
