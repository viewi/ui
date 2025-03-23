<?php

namespace Viewi\UI\Components\Overlay;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\Components\DOM\DomHelper;
use Viewi\Components\DOM\HtmlNode;

class Overlay extends BaseComponent
{
    public string $style = '';
    public bool $absolute = false;
    private int $zIndex = 1110;
    public ?HtmlNode $base = null;
    public bool $backdrop = false;
    public bool $transparent = false;
    public $onDocumentClick = null;
    public ?HtmlNode $area = null;

    public function __construct(private OverlayStack $stack, private DomHelper $dom) {}

    public function mounted()
    {
        // issue next stack
        $this->zIndex = $this->stack->getZIndex();
        $this->calculateStyle();
        $this->trackOutside();
    }

    public function calculateStyle()
    {
        $position = '';
        if ($this->base) {
            $contentBox = $this->base->getBoundingClientRect();
            $window = $this->dom->getWindow();
            $top = $contentBox->bottom + $window->scrollY;
            $left = $contentBox->left + $window->scrollX;
            $position = "top: {$top}px; left: {$left}px; width: {$contentBox->width}px; max-height: 310px;";
        }
        $this->style = "display: block; z-index: {$this->zIndex}; $position";
    }

    public function trackOutside()
    {
        $this->onDocumentClick = function (DomEvent $event) {
            if (
                $this->area !== $event->target
                && !$this->area->contains($event->target)
            ) {
                // click is outside
                $this->onOutside();
            }
        };
        $this->dom->getDocument()?->addEventListener('click', $this->onDocumentClick);
    }

    public function destroy()
    {
        // dispose stack
        $this->stack->put($this->zIndex);
        $this->dom->getDocument()?->removeEventListener('click', $this->onDocumentClick);
    }

    public function onOutside()
    {
        $this->emitEvent('clickOutside');
    }
}
