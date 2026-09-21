<?php

namespace Viewi\UI\Components\Dropdown;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\Components\DOM\HtmlNode;
use Viewi\DI\Inject;
use Viewi\DI\Scope;
use Viewi\UI\Components\Forms\FormContext;
use Viewi\UI\Components\Validation\ValidationMessage;

/**
 * A select with a styled popup, operable from the keyboard the way a native select is
 * (WAI-ARIA combobox + listbox): Tab reaches it; ArrowDown/ArrowUp/Enter/Space open it on the
 * current value; inside, the arrows move, Home/End jump, a typed letter jumps to the next option
 * starting with it, Enter/Space choose, Escape closes, Tab closes and moves on. Focus stays on the
 * field throughout — the highlighted option is announced through aria-activedescendant.
 */
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
    /**
     * The items as options — a PROPERTY rebuilt when items change (a foreach over a method would
     * never re-render).
     * @var SelectInputOption[]
     */
    public array $options = [];
    /** The keyboard cursor: -1 = the placeholder row (nullable), 0.. = an option's position. */
    public int $cursor = 0;
    public string $listId = '';
    public string $activeDescendant = '';

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
        $options = [];
        $position = 0;
        foreach ($this->items as $key => $item) {
            $option = new SelectInputOption();
            $option->position = $position;
            $option->raw = $this->associative ? $key : $item;
            $option->title = $this->associative ? '' . $item : ($this->itemTitle ? '' . $item->{$this->itemTitle} : '' . $item);
            $options[] = $option;
            $position++;
        }
        $this->options = $options;
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
        $this->listId = ($this->id ?? $this->name ?? $this->__id) . '-list';
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
        $this->close();
        $this->emitEvent('model', $item !== null ? ($this->itemValue ? $item->{$this->itemValue} : $item) : null);
    }

    public function toggleExpand()
    {
        if ($this->isExpanded) {
            $this->close();
            return;
        }
        $this->open();
    }

    /** Open with the cursor on the current value, as a native select does. */
    public function open()
    {
        $this->isExpanded = true;
        $this->moveCursor($this->currentPosition());
    }

    public function close()
    {
        $this->isExpanded = false;
        $this->activeDescendant = '';
    }

    public function choose(SelectInputOption $option)
    {
        $this->selectItem($option->raw, $option->title);
        $this->focusField();
    }

    public function choosePlaceholder()
    {
        $this->selectItem(null);
        $this->focusField();
    }

    public function onKey(DomEvent $event)
    {
        $key = $event->key;
        if (!$this->isExpanded) {
            if ($key === 'ArrowDown' || $key === 'ArrowUp' || $key === 'Enter' || $key === ' ') {
                $event->preventDefault();
                $this->open();
            }
            return;
        }
        $first = $this->nullable ? -1 : 0;
        $last = count($this->options) - 1;
        if ($key === 'ArrowDown') {
            $event->preventDefault();
            $this->moveCursor(min($this->cursor + 1, $last));
        } else if ($key === 'ArrowUp') {
            $event->preventDefault();
            $this->moveCursor(max($this->cursor - 1, $first));
        } else if ($key === 'Home') {
            $event->preventDefault();
            $this->moveCursor($first);
        } else if ($key === 'End') {
            $event->preventDefault();
            $this->moveCursor($last);
        } else if ($key === 'Enter' || $key === ' ') {
            $event->preventDefault();
            if ($this->cursor === -1) {
                $this->selectItem(null);
            } else if ($this->cursor >= 0 && $this->cursor <= $last) {
                $option = $this->options[$this->cursor];
                $this->selectItem($option->raw, $option->title);
            }
        } else if ($key === 'Escape') {
            $event->preventDefault();
            $this->close();
        } else if ($key === 'Tab') {
            $this->close();
        } else if (strlen($key) === 1) {
            $this->typeAhead(strtolower($key));
        }
    }

    /** Jump to the next option whose title starts with the letter, wrapping round. */
    private function typeAhead(string $letter)
    {
        $total = count($this->options);
        for ($step = 1; $step <= $total; $step++) {
            $position = ($this->cursor + $step + $total) % $total;
            if (strpos(strtolower($this->options[$position]->title), $letter) === 0) {
                $this->moveCursor($position);
                return;
            }
        }
    }

    private function moveCursor(int $position)
    {
        $this->cursor = $position;
        $this->activeDescendant = $this->listId . '-' . ($position === -1 ? 'none' : $position);
        // Keep the highlighted option visible in a long list; it may not have rendered yet on open.
        <<<'javascript'
        setTimeout(() => {
            const option = document.getElementById($this.activeDescendant);
            if (option && option.scrollIntoView) {
                option.scrollIntoView({ block: 'nearest' });
            }
        }, 0);
        javascript;
    }

    /** Where the current value sits in the list; the placeholder or the first option otherwise. */
    private function currentPosition(): int
    {
        foreach ($this->options as $option) {
            if ($this->isCurrent($option)) {
                return $option->position;
            }
        }
        return $this->nullable ? -1 : 0;
    }

    public function isCurrent(SelectInputOption $option): bool
    {
        if ($this->currentValue === null) {
            return false;
        }
        return $option->raw === $this->currentValue
            || ($this->associative && $option->title === $this->currentValue);
    }

    private function focusField()
    {
        if ($this->input !== null) {
            $this->input->focus();
        }
    }

    public function getTitle()
    {
        if ($this->currentValue) {
            // plain (string/int) items have no title captured until selectItem —
            // fall back to the value itself so the initial model renders
            return $this->itemTitle ? $this->currentValue->{$this->itemTitle} : ($this->currentTitle !== null ? $this->currentTitle : $this->currentValue);
        }
        return $this->placeholder;
    }
}
