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
    public FormContext $formContext;
    public array $rules = [];

    public function __construct(
        #[Inject(Scope::COMPONENT)]
        private FormContext $form
    ) {
    }

    public function init()
    {
    }

    public function handleSubmit(DomEvent $event)
    {
        $this->emitEvent('submit', $event);
    }

    public function validate(): bool
    {
        $finalValid = true;
        $generalMessages = [];
        foreach ($this->rules as $field => $rules) {
            $messages = [];
            foreach ($rules as $_ => $action) {
                $validationResult = $action();
                $valid = $validationResult === true;
                $finalValid = $finalValid && $valid;
                if (!$valid) {
                    $messages[] = $validationResult || 'Validation has failed';
                }
            }
            /**
             * @var callback
             */
            $inputCallback = $this->form->inputs[$field] ?? false;
            if (count($messages) > 0) {
                if ($inputCallback) {
                    $inputCallback(false, $messages);
                } else {
                    $generalMessages = array_merge($generalMessages, $messages);
                }
            } else {
                if ($inputCallback) {
                    $inputCallback(true, $messages);
                }
            }
        }
        /**
         * @var callback
         */
        $fallbackMessagesCallback = $this->form->inputs['fallbackMessages'] ?? false;

        if ($fallbackMessagesCallback) {
            if ($generalMessages) {
                $fallbackMessagesCallback(false, $generalMessages);
            } else {
                $fallbackMessagesCallback(true, $generalMessages);
            }
        }
        return $finalValid; // $valid;
    }
}
