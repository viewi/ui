<?php

namespace Viewi\UI\Components\Inputs;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\Components\DOM\HtmlNode;
use Viewi\DI\Inject;
use Viewi\DI\Scope;
use Viewi\UI\Components\Forms\FormContext;
use Viewi\UI\Components\Validation\ValidationMessage;

class BaseInput extends BaseComponent
{
    public ?string $type = null;
    public ?string $placeholder = null;
    public ?string $label = null;
    public ?string $hint = null;
    public ?string $autocomplete = null;
    public ?string $inputClass = null;
    public ?string $wrapperClass = null;
    public ?string $id = null;
    private ?ValidationMessage $validationMessages = null;
    public $isInvalid = false;
    public bool $inset = false;

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
}
