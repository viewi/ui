<?php

namespace Viewi\UI\Components\Dropdown;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\HtmlNode;
use Viewi\DI\Inject;
use Viewi\DI\Scope;
use Viewi\UI\Components\Forms\FormContext;
use Viewi\UI\Components\Validation\ValidationMessage;

class SelectInput extends BaseComponent
{
    public array $items = [];
    public ?string $label = null;
    public ?string $hint = null;
    public string $placeholder = 'Please select';
    public ?string $inputClass = null;
    public ?string $wrapperClass = null;
    public ?string $id = null;
    public ?string $name = null;
    public ?string $model = null;
    public $currentValue = null;
    public $currentTitle = null;
    public bool $nullable = false;
    public ?string $itemTitle = null;
    public ?string $itemValue = null;
    public bool $isExpanded = false;
    public bool $associative = false;
    private ?ValidationMessage $validationMessages = null;
    public $isInvalid = false;
    public ?HtmlNode $input = null;

    public function __construct(
        #[Inject(Scope::PARENT)]
        private ?FormContext $form = null
    ) {}

    public function init()
    {
        $this->watch('model', fn() => $this->onDataChange());
        $this->watch('items', fn() => $this->onDataChange());
    }

    public function onDataChange()
    {
        foreach ($this->items as $item) {
            $itemValue = $this->itemValue ? $item->{$this->itemValue} : $item;
            if ($itemValue === $this->model) {
                $this->currentValue = $item;
                break;
            }
        }
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
        $this->onDataChange();
    }

    public function selectItem($item, $title = null)
    {
        $this->validationMessages->show = false;
        $this->isInvalid = false;
        $this->currentValue = $item;
        $this->currentTitle = $title;
        $this->isExpanded = false;
        $this->emitEvent('model', $item !== null ? ($this->itemValue ? $item->{$this->itemValue} : $item) : null);
    }

    public function toggleExpand()
    {
        $this->isExpanded = !$this->isExpanded;
    }

    public function getTitle()
    {
        if ($this->currentValue) {
            return $this->itemTitle ? $this->currentValue->{$this->itemTitle} : $this->currentTitle;
        }
        return $this->placeholder;
    }
}
