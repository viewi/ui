<?php

namespace Viewi\UI\Components\Validation;

use Viewi\Components\BaseComponent;
use Viewi\UI\Components\Forms\FormContext;

class ValidationMessage extends BaseComponent
{
    public bool $show = false;
    public array $messages = [];
    public bool $fallback = false;
    public ?string $wrapperClass = null;

    public function __construct(
        // #[Inject(Scope::PARENT)]
        // private FormContext $form
    ) {
        // print_r($this->form->inputs);
    }

    public function mounted()
    {
        if ($this->fallback) {
            /**
             * @var ?FormContext $formContext
             */
            $formContext = $this->inject('FormContext');
            if ($formContext !== null) {
                $formContext->inputs['fallbackMessages'] = function ($valid, $errors) {
                    $this->show = !$valid;
                    $this->messages = $errors;
                };
            }
        }
    }
}
