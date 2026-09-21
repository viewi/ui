<?php

namespace Viewi\UI\Components\Inputs;

use Viewi\Components\BaseComponent;
use Viewi\Components\Environment\ClientTimer;
use Viewi\Components\DOM\HtmlNode;

/**
 * A search box: icon, clear button, Escape to clear, a label for screen readers, and an optional
 * debounce so a list backed by the server is not re-fetched on every keystroke.
 *
 *     <SearchInput model="$term" (search)="reload" debounce="300" placeholder="Search" />
 *
 * `model` follows every keystroke (two-way); `search` fires once typing pauses for `debounce` ms
 * (0 = on every change) and immediately on clear.
 */
class SearchInput extends BaseComponent
{
    public ?string $id = null;
    /** Visually hidden, but always present: a search box needs an accessible name. */
    public string $label = 'Search';
    public string $placeholder = 'Search';
    public ?string $hint = null;
    public ?string $model = null;
    public int $debounce = 300;
    public string $inputClass = '';
    /**
     * What the box shows; kept apart from `model` so a parent's update and typing do not loop.
     * Not named `value`: Viewi compiles `model="$value"` into a setter whose own parameter is
     * `$value`, so the assignment lands on the parameter and typing never reaches the component.
     */
    public string $term = '';
    /** The input's id when none is given. Props are not set yet in init(), so `id` is read in the template. */
    public string $autoId = '';
    public ?HtmlNode $field = null;
    private ?int $timer = null;
    private string $lastEmitted = '';

    public function init()
    {
        // Viewi calls init() BEFORE it sets the props, so `model` and `id` are still their defaults
        // here: the initial model arrives through the watcher, and the id is resolved in the template.
        $this->autoId = 'search-' . $this->__id;
        $this->watch('model', fn() => $this->fromParent());
        $this->watch('term', fn() => $this->changed());
    }

    /** The parent changed the model (a restored search, a "clear" elsewhere): show it. */
    private function fromParent()
    {
        $incoming = $this->model ?? '';
        if ($incoming !== $this->term) {
            $this->lastEmitted = $incoming;
            $this->term = $incoming;
        }
    }

    private function changed()
    {
        if ($this->term === $this->lastEmitted) {
            return;
        }
        $this->lastEmitted = $this->term;
        $this->emitEvent('model', $this->term);
        if ($this->debounce <= 0) {
            $this->emitEvent('search', $this->term);
            return;
        }
        if ($this->timer !== null) {
            ClientTimer::clearTimeoutStatic($this->timer);
        }
        $this->timer = ClientTimer::setTimeoutStatic(fn() => $this->emitEvent('search', $this->term), $this->debounce);
    }

    /** Clear and search at once — nobody waits for a debounce after pressing ×. */
    public function clear()
    {
        if ($this->timer !== null) {
            ClientTimer::clearTimeoutStatic($this->timer);
            $this->timer = null;
        }
        $wasEmpty = $this->term === '';
        $this->lastEmitted = '';
        $this->term = '';
        if (!$wasEmpty) {
            $this->emitEvent('model', '');
            $this->emitEvent('search', '');
        }
        if ($this->field !== null) {
            $this->field->focus();
        }
    }
}
