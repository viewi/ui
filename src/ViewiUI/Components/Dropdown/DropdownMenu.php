<?php

namespace Viewi\UI\Components\Dropdown;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\Components\DOM\DomHelper;
use Viewi\Components\DOM\HtmlNode;
use Viewi\Components\Environment\ClientTimer;

/**
 * An actions menu behind one small trigger — the ⋯ on a row:
 *
 *     <DropdownMenu items="$folderActions" label="Folder actions" (select)="onFolderAction" />
 *
 * `items` is a list of DropdownMenuItem; choosing one emits `select` with its key. The menu opens in an
 * Overlay (portalled to <body>, so a scrolling panel or a table cell never clips it) and follows
 * the WAI-ARIA menu-button pattern, because a menu a keyboard cannot operate is a menu some people
 * cannot use at all:
 *
 *  - the trigger is a real button (Tab reaches it) with aria-haspopup and aria-expanded;
 *  - Enter, Space or ArrowDown opens it on the first item, ArrowUp on the last;
 *  - inside, ArrowUp/ArrowDown move (wrapping, skipping disabled items), Home/End jump,
 *    Enter/Space choose, Escape closes and puts focus back on the trigger, Tab closes;
 *  - a click outside closes it.
 *
 * The trigger stops the click from bubbling, so a menu inside a clickable row does not also select
 * the row. It must sit BESIDE a row's own button, never inside it: a button inside a button is not
 * valid HTML, and browsers resolve it unpredictably.
 */
class DropdownMenu extends BaseComponent
{
    /** @var DropdownMenuItem[] */
    public array $items = [];
    /** Accessible name for the trigger and the menu — say what the actions are for. */
    public string $label = 'Actions';
    public string $icon = 'bi-three-dots';
    public string $buttonClass = 'btn btn-sm btn-link dropdown-menu-trigger';
    /** 'end' opens leftward from the trigger's right edge — the usual place for a row's ⋯. */
    public string $align = 'end';
    public bool $isOpen = false;
    public ?HtmlNode $trigger = null;
    public ?HtmlNode $menu = null;
    /** Index of the focused item while the menu is open; -1 = none. */
    public int $activeIndex = -1;

    public function toggle()
    {
        if ($this->isOpen) {
            $this->close(true);
            return;
        }
        $this->open(0);
    }

    /** Open with focus on an item: 0 = first, -1 = last. Focus moves once the menu has rendered. */
    public function open(int $index)
    {
        $this->isOpen = true;
        $this->activeIndex = $index === -1 ? $this->lastEnabled() : $this->nextEnabled(-1, 1);
        ClientTimer::setTimeoutStatic(fn() => $this->focusActive(), 0);
    }

    /** Close; with $returnFocus the trigger gets focus back, as keyboard users expect after Escape. */
    public function close(bool $returnFocus = false)
    {
        if (!$this->isOpen) {
            return;
        }
        $this->isOpen = false;
        $this->activeIndex = -1;
        if ($returnFocus && $this->trigger !== null) {
            $this->trigger->focus();
        }
    }

    public function onOutside()
    {
        $this->close(false);
    }

    public function choose(DropdownMenuItem $item)
    {
        if ($item->disabled) {
            return;
        }
        $this->close(true);
        $this->emitEvent('select', $item->key);
    }

    public function onTriggerKey(DomEvent $event)
    {
        $key = $event->key;
        if ($key === 'ArrowDown' || $key === 'Enter' || $key === ' ') {
            $event->preventDefault();
            $this->open(0);
        } else if ($key === 'ArrowUp') {
            $event->preventDefault();
            $this->open(-1);
        }
    }

    public function onMenuKey(DomEvent $event)
    {
        $key = $event->key;
        if ($key === 'ArrowDown') {
            $event->preventDefault();
            $this->activeIndex = $this->nextEnabled($this->activeIndex, 1);
            $this->focusActive();
        } else if ($key === 'ArrowUp') {
            $event->preventDefault();
            $this->activeIndex = $this->nextEnabled($this->activeIndex, -1);
            $this->focusActive();
        } else if ($key === 'Home') {
            $event->preventDefault();
            $this->activeIndex = $this->nextEnabled(-1, 1);
            $this->focusActive();
        } else if ($key === 'End') {
            $event->preventDefault();
            $this->activeIndex = $this->lastEnabled();
            $this->focusActive();
        } else if ($key === 'Escape') {
            $event->preventDefault();
            $this->close(true);
        } else if ($key === 'Tab') {
            // Let Tab move on as it would anyway; the menu just gets out of the way.
            $this->close(false);
        }
    }

    /**
     * Move DOM focus to the active item. The Overlay renders hidden until rendered() has measured
     * its position (one timer tick, then one more for the re-render), and a hidden element cannot
     * take focus — so try, and if focus did not land, try again on the next frames, up to 10 times.
     */
    public function focusActive(int $tries = 10)
    {
        if ($this->menu === null || $this->activeIndex < 0) {
            return;
        }
        $buttons = DomHelper::getDomList($this->menu->querySelectorAll('[role="menuitem"]'));
        if ($this->activeIndex >= count($buttons)) {
            return;
        }
        $target = $buttons[$this->activeIndex];
        $target->focus();
        if (DomHelper::getActiveElement() !== $target && $tries > 0) {
            DomHelper::requestAnimationFrame(fn() => $this->focusActive($tries - 1));
        }
    }

    /** The next enabled item after $from in $step's direction, wrapping around; -1 if none. */
    private function nextEnabled(int $from, int $step): int
    {
        // Not `$count`: a local named like a function it calls shadows that function once
        // transpiled — `var count = count(...)` — and the call fails in the browser.
        $total = count($this->items);
        if ($total === 0) {
            return -1;
        }
        $index = $from;
        for ($i = 0; $i < $total; $i++) {
            $index = ($index + $step + $total) % $total;
            if (!$this->items[$index]->disabled) {
                return $index;
            }
        }
        return -1;
    }

    private function lastEnabled(): int
    {
        return $this->nextEnabled(0, -1);
    }
}
