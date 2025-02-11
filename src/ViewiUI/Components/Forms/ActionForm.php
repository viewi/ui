<?php

namespace Viewi\UI\Components\Forms;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\DI\Inject;
use Viewi\DI\Scope;

class ActionForm extends BaseComponent
{
    public ?string $id = null;
    public ?string $method = null;
    public ?string $classList = null;
    public ?string $action = null;
    public ?string $autocomplete = null;
    public array $rules = [];

    public function __construct(
        #[Inject(Scope::COMPONENT)]
        private FormContext $form
    ) {}

    public function init() {}

    public function handleSubmit(DomEvent $event)
    {
        $this->emitEvent('submit', $event);
    }

    public function validate(): bool
    {
        return $this->form->validate($this->rules);
    }
}
