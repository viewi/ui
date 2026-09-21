<?php

namespace Viewi\UI\Components\Navigation;

use Viewi\Components\BaseComponent;

/**
 * Where you are, with the way back up:
 *
 *     <Breadcrumb items="$crumbs" label="Folder" (select)="openCrumb" />
 *
 * A <nav> with an ordered list, the last item marked aria-current="page" and never a link. Every
 * earlier item with a key is a button that emits `select` with that key. Deep trails collapse their
 * middle on small screens (CSS), so a five-level path does not wrap onto three lines.
 */
class Breadcrumb extends BaseComponent
{
    /** @var BreadcrumbItem[] */
    public array $items = [];
    public string $label = 'Breadcrumb';

    public function select(BreadcrumbItem $item)
    {
        $this->emitEvent('select', $item->key);
    }
}
