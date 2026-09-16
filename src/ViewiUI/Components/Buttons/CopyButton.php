<?php

namespace Viewi\UI\Components\Buttons;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;

/**
 * A button that copies `$value` to the clipboard and says whether it worked.
 *
 * Exists because the hand-rolled version kept getting one of two things wrong: announcing
 * "Copied" before `writeText()` had settled (so a refusal still reported success), or touching
 * `navigator.clipboard` where it does not exist — plain http, some embedded webviews — which
 * throws before any handler runs. Here the icon only turns into a tick once the write resolved,
 * and a refused or missing Clipboard API falls back to a hidden textarea + `execCommand('copy')`
 * before admitting failure.
 *
 * Feedback lives on the button itself (icon + optional label, back to idle after 1.5s) rather
 * than in a toast, so it works on any page without an alert host, and a row of these in a table
 * shows which one was pressed.
 *
 *     <CopyButton value="{$url}" />
 *     <CopyButton value="{$url}" label="Copy link" buttonClass="btn btn-outline-secondary btn-sm" />
 */
class CopyButton extends BaseComponent
{
    public string $value = '';
    /** Visible text beside the icon; empty = icon-only (the title/aria-label still name it). */
    public string $label = '';
    public string $title = 'Copy';
    public string $buttonClass = 'btn btn-link btn-sm p-0';

    /**
     * '' (idle), 'Copied' or 'Copy failed' — set from the browser only, so SSR always renders idle.
     * The template picks the icon with one literal <Icon> per state rather than a bound name: the
     * icon sprite keeps only symbols whose names appear literally in the build, and a name that
     * exists only at runtime renders as an empty box.
     */
    public string $status = '';

    public function copy(DomEvent $event)
    {
        $event->preventDefault();
        $event->stopPropagation();
        <<<'javascript'
        var text = $this.value || '';
        var settle = function (ok) {
            $this.status = ok ? 'Copied' : 'Copy failed';
            window.setTimeout(function () {
                $this.status = '';
            }, 1500);
        };
        var fallback = function () {
            var ok = false;
            try {
                var area = document.createElement('textarea');
                area.value = text;
                area.setAttribute('readonly', '');
                area.style.position = 'fixed';
                area.style.opacity = '0';
                document.body.appendChild(area);
                area.select();
                ok = document.execCommand('copy');
                document.body.removeChild(area);
            } catch (e) {
                ok = false;
            }
            settle(ok);
        };
        if (window.isSecureContext && navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () { settle(true); }, fallback);
        } else {
            fallback();
        }
        javascript;
    }
}
