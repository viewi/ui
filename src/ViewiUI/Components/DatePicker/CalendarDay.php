<?php

namespace Viewi\UI\Components\DatePicker;

/**
 * One cell in the DateRangePicker month grid. Plain model (not a component) — rebuilt
 * en-masse on every view/selection change so the grid foreach stays a reactive property.
 */
class CalendarDay
{
    public function __construct(
        public string $date = '',   // 'YYYY-MM-DD' (UTC)
        public int $day = 0,        // day-of-month number shown in the cell
        public bool $inMonth = false,  // false → leading/trailing day of an adjacent month (muted)
        public bool $isStart = false,  // selected range start
        public bool $isEnd = false,    // selected range end
        public bool $inRange = false,  // strictly between start and end
        public bool $isToday = false,
        public bool $disabled = false  // future day — no analytics data ahead of "now"
    ) {}
}
