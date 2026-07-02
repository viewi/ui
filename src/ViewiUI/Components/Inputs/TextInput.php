<?php

namespace Viewi\UI\Components\Inputs;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\Components\DOM\HtmlNode;
use Viewi\DI\Inject;
use Viewi\DI\Scope;
use Viewi\UI\Components\Forms\FormContext;
use Viewi\UI\Components\Validation\ValidationMessage;

class TextInput extends BaseComponent
{
    public ?string $type = null;
    public ?string $placeholder = null;
    public ?string $label = null;
    public ?string $hint = null;
    public ?string $autocomplete = null;
    public ?string $inputClass = null;
    public ?string $wrapperClass = null;
    public ?string $id = null;
    public ?string $name = null;
    public ?string $model = null;
    public ?HtmlNode $input = null;
    public bool $textarea = false;
    public bool $readonly = false;
    public ?int $rows = null;
    public ?int $cols = null;
    private ?ValidationMessage $validationMessages = null;
    public $isInvalid = false;
    public bool $inset = false;
    public bool $clearable = false;
    // Password-style helpers: an eye toggle that unmasks the field, and a copy-to-clipboard button.
    // Handy for reversible secrets (e.g. a short-link access password) the owner needs to view/re-share.
    public bool $revealable = false;
    public bool $copyable = false;
    public bool $revealed = false;
    public bool $copied = false;

    public function __construct(
        #[Inject(Scope::PARENT)]
        private ?FormContext $form = null
    ) {}

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

    public function onInput(DomEvent $event)
    {
        $this->onContentChange($this->type === 'number' && !$event->target->value ? null : $event->target->value);
    }

    public function onContentChange($content)
    {
        $this->validationMessages->show = false;
        $this->isInvalid = false;
        $this->emitEvent('model', $content);
        $this->emitEvent('input', $content);
    }

    public function clear()
    {
        $this->onContentChange(null);
        $this->emitEvent('clear', true);
    }

    public function toggleReveal()
    {
        $this->revealed = !$this->revealed;
    }

    public function copyValue(DomEvent $event)
    {
        $event->preventDefault();
        <<<'javascript'
        navigator.clipboard.writeText($this.model || '').then(() => {
        $this.copied = true;
        setTimeout(() => { $this.copied = false; }, 1500);
        });
        javascript;
    }
}
