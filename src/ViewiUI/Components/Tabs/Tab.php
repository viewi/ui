<?php

namespace Viewi\UI\Components\Tabs;

use Viewi\Components\BaseComponent;
use Viewi\DI\Inject;
use Viewi\DI\Scope;

class Tab extends BaseComponent
{
    public ?string $id = null;
    public ?string $classList = null;
    public string $title = '';
    public bool $active = false;
    public TabItem $tabModel;

    public function __construct(
        #[Inject(Scope::PARENT)]
        private TabsContext $context
    ) {}


    public function mounted()
    {
        $this->tabModel = new TabItem($this->title, $this->active);
        $this->context->addTab($this->tabModel);
    }
}
