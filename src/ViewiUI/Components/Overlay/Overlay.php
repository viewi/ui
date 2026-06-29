<?php

namespace Viewi\UI\Components\Overlay;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\Components\DOM\DomHelper;
use Viewi\Components\DOM\HtmlNode;

class Overlay extends BaseComponent
{
    // Pre-position state: fixed (out of document flow, so it can't grow the page and toggle a
    // scrollbar) + hidden (no flash at the wrong spot). calculateStyle() swaps in the final
    // positioned, visible style on the `rendered()` hook. A transient scrollbar would otherwise
    // narrow the layout and skew the base measurement by the scrollbar width.
    public string $style = 'position: fixed; top: -1000px; left: -1000px; visibility: hidden;';
    public bool $absolute = false;
    private int $zIndex = 1110;
    public ?HtmlNode $base = null;
    public bool $backdrop = false;
    public bool $transparent = false;
    public $onDocumentClick = null;
    public ?HtmlNode $area = null;
    /**
     * Horizontal anchor relative to the base element:
     *   'start' → overlay left edge aligns with base left edge (default; opens rightward)
     *   'end'   → overlay right edge aligns with base right edge (opens leftward; for right-aligned triggers)
     * Either way the result is clamped into the viewport so it never runs off an edge.
     */
    public string $align = 'start';
    /**
     * false (default): overlay width matches the base (dropdown/autocomplete style — content fills it).
     * true: width follows the slot content (measured at mount); use for poppers wider than their trigger
     * (calendars, filter menus). Required for correct edge-clamping of wide content, and only meaningful
     * together with `absolute` (so the area shrinks to its content).
     */
    public bool $autoWidth = false;
    private int $viewportMargin = 8; // px kept between the overlay and the viewport edge when clamping

    public function __construct(private OverlayStack $stack, private DomHelper $dom) {}

    public function mounted()
    {
        // issue next stack
        $this->zIndex = $this->stack->getZIndex();
        $this->trackOutside();
    }

    public function rendered()
    {
        $this->calculateStyle();
    }

    public function calculateStyle()
    {
        $position = '';
        if ($this->base) {
            $contentBox = $this->base->getBoundingClientRect();
            $window = $this->dom->getWindow();
            $top = $contentBox->bottom + $window->scrollY;

            // The overlay's effective width: the base width (default) or the measured content width (autoWidth).
            $width = $contentBox->width;
            if ($this->autoWidth && $this->area) {
                $areaBox = $this->area->getBoundingClientRect();
                $width = $areaBox->width;
            }

            // Anchor horizontally to the base (left or right edge). Only nudge the overlay when the
            // anchored position would actually fall outside the viewport — when it already fits, honor
            // the alignment exactly (no artificial edge-margin shift). The margin applies only while
            // rescuing a genuinely overflowing overlay.
            $left = $this->align === 'end' ? ($contentBox->right - $width) : $contentBox->left;
            if ($left + $width > $window->innerWidth) {
                $left = $window->innerWidth - $width - $this->viewportMargin;
            }
            if ($left < 0) {
                $left = $this->viewportMargin;
            }
            $left = $left + $window->scrollX;

            $widthRule = $this->autoWidth ? '' : "width: {$contentBox->width}px; ";
            $position = "top: {$top}px; left: {$left}px; {$widthRule}max-height: 310px;";
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
