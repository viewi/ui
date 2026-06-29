<?php

namespace Viewi\UI\Components\DatePicker;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\HtmlNode;

/**
 * Calendar date-range picker. A trigger button opens an Overlay holding a month grid;
 * click a start day then an end day to pick an inclusive range. Emits `apply` with
 * [from, to] as 'YYYY-MM-DD' (UTC) strings. Bind `from`/`to` to reflect external changes
 * (e.g. preset buttons clearing the custom range).
 *
 * All date math is UTC (gmmktime/gmdate) to match the server, which interprets the
 * query dates as UTC — keeps the grid and the fetched window from drifting by a day.
 */
class DateRangePicker extends BaseComponent
{
    public ?string $from = null;   // bound current range start ('YYYY-MM-DD' or null/'')
    public ?string $to = null;     // bound current range end
    public ?string $placeholder = 'Custom range'; // trigger label when no range is set

    public bool $isExpanded = false;
    public ?HtmlNode $trigger = null; // ref the Overlay positions against

    // --- calendar view state (all derived; rebuilt as a whole) ---
    public int $viewYear = 2000;
    public int $viewMonth = 1;
    public string $selStart = '';   // in-progress selection start ('YYYY-MM-DD')
    public string $selEnd = '';     // in-progress selection end
    public array $days = [];        // 42 CalendarDay cells (6 weeks) — a property so foreach re-renders
    public string $monthLabel = ''; // "June 2026"
    public string $selectionLabel = '';
    public string $triggerLabel = 'Custom range'; // sane SSR default; refined from props in mounted()

    public function init()
    {
        // Re-sync when the parent changes the bound range (e.g. a preset clears from/to).
        $this->watch('from', fn() => $this->syncFromProps());
        $this->watch('to', fn() => $this->syncFromProps());
    }

    public function mounted()
    {
        $this->syncFromProps();
    }

    /** Seconds-since-epoch for a 'YYYY-MM-DD' string, interpreted as UTC. */
    private function ts(string $ymd): int
    {
        return strtotime($ymd . ' UTC');
    }

    private function today(): string
    {
        return gmdate('Y-m-d', time());
    }

    /** Pull the selection + the visible month from the bound from/to props. */
    public function syncFromProps()
    {
        $this->selStart = $this->from ?? '';
        $this->selEnd = $this->to ?? '';
        $anchor = $this->selEnd !== '' ? $this->selEnd : ($this->selStart !== '' ? $this->selStart : $this->today());
        $anchorTs = $this->ts($anchor);
        $this->viewYear = intval(gmdate('Y', $anchorTs));
        $this->viewMonth = intval(gmdate('n', $anchorTs));
        $this->rebuild();
        $this->refreshLabel();
    }

    /** Recompute the 42-cell grid + month/selection labels for the current view + selection. */
    public function rebuild()
    {
        $firstTs = gmmktime(0, 0, 0, $this->viewMonth, 1, $this->viewYear);
        $this->monthLabel = gmdate('F Y', $firstTs);
        $dow = intval(gmdate('N', $firstTs)); // 1 = Monday .. 7 = Sunday
        $startTs = $firstTs - ($dow - 1) * 86400; // back up to the Monday on/before the 1st
        $todayStr = $this->today();
        $cells = [];
        $i = 0;
        while ($i < 42) {
            $ts = $startTs + $i * 86400;
            $date = gmdate('Y-m-d', $ts);
            $cells[] = new CalendarDay(
                $date,
                intval(gmdate('j', $ts)),
                intval(gmdate('n', $ts)) === $this->viewMonth,
                $date === $this->selStart,
                $date === $this->selEnd,
                $this->selStart !== '' && $this->selEnd !== '' && $date > $this->selStart && $date < $this->selEnd,
                $date === $todayStr,
                $date > $todayStr
            );
            $i = $i + 1;
        }
        $this->days = $cells;
        $this->refreshSelectionLabel();
    }

    private function refreshSelectionLabel()
    {
        if ($this->selStart === '') {
            $this->selectionLabel = 'Select a start date';
        } elseif ($this->selEnd === '') {
            $this->selectionLabel = gmdate('M j, Y', $this->ts($this->selStart)) . ' → …';
        } else {
            $this->selectionLabel = gmdate('M j', $this->ts($this->selStart))
                . ' – ' . gmdate('M j, Y', $this->ts($this->selEnd));
        }
    }

    /** Trigger button label: the active range, or the placeholder when none is set. */
    public function refreshLabel()
    {
        if ($this->from && $this->to) {
            $this->triggerLabel = gmdate('M j', $this->ts($this->from)) . ' – ' . gmdate('M j', $this->ts($this->to));
        } else {
            $this->triggerLabel = $this->placeholder ?? 'Custom range';
        }
    }

    public function toggleOpen()
    {
        $this->isExpanded = !$this->isExpanded;
        if ($this->isExpanded) {
            $this->syncFromProps(); // open on the current range
        }
    }

    public function prevMonth()
    {
        $this->viewMonth = $this->viewMonth - 1;
        if ($this->viewMonth < 1) {
            $this->viewMonth = 12;
            $this->viewYear = $this->viewYear - 1;
        }
        $this->rebuild();
    }

    public function nextMonth()
    {
        $this->viewMonth = $this->viewMonth + 1;
        if ($this->viewMonth > 12) {
            $this->viewMonth = 1;
            $this->viewYear = $this->viewYear + 1;
        }
        $this->rebuild();
    }

    /** First click sets the start (clears end); second click completes the range (or restarts if earlier). */
    public function pickDay($cell)
    {
        if ($cell->disabled) {
            return;
        }
        if ($this->selStart === '' || $this->selEnd !== '') {
            $this->selStart = $cell->date;
            $this->selEnd = '';
        } elseif ($cell->date < $this->selStart) {
            $this->selStart = $cell->date;
        } else {
            $this->selEnd = $cell->date;
        }
        $this->rebuild();
    }

    /** Commit the selection. A lone start day applies as a single-day range. */
    public function apply()
    {
        if ($this->selStart === '') {
            return;
        }
        $end = $this->selEnd !== '' ? $this->selEnd : $this->selStart;
        $this->isExpanded = false;
        $this->emitEvent('apply', [$this->selStart, $end]);
    }

    /** Close without applying; revert the in-progress selection to the bound range. */
    public function cancel()
    {
        $this->isExpanded = false;
        $this->syncFromProps();
    }
}
