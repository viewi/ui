<?php

namespace Viewi\UI\Components\EmptyState;

use Viewi\Components\BaseComponent;

/**
 * A centred "nothing here yet" placeholder for an empty list or screen: a soft
 * icon tile, a short title, an optional line of explanation, and an optional
 * call to action.
 *
 * An empty screen is the FIRST thing a new user sees, so it should explain what
 * belongs here and offer the action that fills it — a blank area reads as broken.
 *
 * Either pass `actionText` + `actionLink` for a link CTA, or fill the `action`
 * slot when the button needs a click handler (see DataTable, which wires it to
 * the same create action as its Add button).
 */
class EmptyState extends BaseComponent
{
    public ?string $id = null;
    public string $icon = 'bi-inbox';
    public string $title = 'Nothing here yet';
    public string $description = '';
    public string $actionText = '';
    public ?string $actionLink = null;
    public string $classList = '';
}
