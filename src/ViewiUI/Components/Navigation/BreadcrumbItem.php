<?php

namespace Viewi\UI\Components\Navigation;

/**
 * One step of a Breadcrumb. `key` is what `select` reports when it is clicked; null makes it plain
 * text (the last item, where you already are, is never a link).
 */
class BreadcrumbItem
{
    public function __construct(
        public string $label,
        public $key = null,
        public string $icon = ''
    ) {
    }
}
