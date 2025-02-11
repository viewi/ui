<?php

namespace Viewi\UI\Components\Dropdown;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\Components\DOM\HtmlNode;
use Viewi\DI\Inject;
use Viewi\DI\Scope;
use Viewi\UI\Components\Forms\FormContext;
use Viewi\UI\Components\Validation\ValidationMessage;

class Dropdown extends BaseComponent
{
    public array $options = [];
    public ?string $label = null;
    public ?string $hint = null;
    public ?string $inputClass = null;
    public ?string $wrapperClass = null;
    public ?string $id = null;
    public ?string $name = null;
    public ?string $model = null;
    public bool $nullable = false;
    public ?HtmlNode $input = null;
    private ?ValidationMessage $validationMessages = null;
    public $isInvalid = false;

    public function __construct(
        #[Inject(Scope::PARENT)]
        private ?FormContext $form = null
    ) {
    }

    public function mounted()
    {
        if ($this->form !== null) {
            $this->form->inputs[$this->id ?? $this->name ?? $this->__id] = function ($valid, $errors) {
                $this->isInvalid = !$valid;
                $this->validationMessages->show = $this->isInvalid;
                $this->validationMessages->messages = $errors;
            };
        }
    }

    public function onChange(DomEvent $event)
    {
        $this->validationMessages->show = false;
        $this->isInvalid = false;
        $this->emitEvent('model', $this->nullable ? $event->target->value || null : $event->target->value);
    }
}
