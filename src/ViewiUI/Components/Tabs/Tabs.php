<?php

namespace Viewi\UI\Components\Tabs;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\DI\Inject;
use Viewi\DI\Scope;

class Tabs extends BaseComponent
{
    public ?string $id = null;
    public ?string $classList = null;
    public string $portalName = '';

    public function __construct(
        #[Inject(Scope::COMPONENT)]
        public TabsContext $context
    ) {}

    public function mounted()
    {
        $this->portalName = "Tabs-{$this->__id}";
    }

    public function selectTab(DomEvent $event, TabItem $tabItem)
    {
        $event->preventDefault();
        $this->context->selectTab($tabItem);
    }
}
