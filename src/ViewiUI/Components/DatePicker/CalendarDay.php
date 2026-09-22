<?php

namespace Viewi\UI\Components\DatePicker;

/**
 * One cell in the DateRangePicker month grid: only what belongs to the MONTH. Built once per
 * visible month and reused while the selection changes, so the day buttons keep their elements
 * (and focus); whether a cell is the start, the end or in between is asked of the picker.
 */
class CalendarDay
{
    public function __construct(
        public string $date = '',   // 'YYYY-MM-DD' (UTC)
        public int $day = 0,        // day-of-month number shown in the cell
        public bool $inMonth = false,  // false → leading/trailing day of an adjacent month (muted)
        public bool $isToday = false,
        public bool $disabled = false  // future day - no analytics data ahead of "now"
    ) {}
}
