<?php

namespace Viewi\UI\Components\Accordion;

use Viewi\Components\BaseComponent;
use Viewi\DI\Inject;
use Viewi\DI\Scope;

class Accordion extends BaseComponent
{
    public ?string $id = null;
    public ?string $classList = null;
    public bool $multiple = false;

    public function __construct(
        #[Inject(Scope::COMPONENT)]
        private AccordionContext $context
    ) {}

    public function mounted()
    {
        $this->context->multiple = $this->multiple;
    }
}
