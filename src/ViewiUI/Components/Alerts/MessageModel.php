<?php

namespace Viewi\UI\Components\Alerts;

class MessageModel
{
    public bool $show = true;
    public ?string $id = null;
    /**
     * An optional action offered beside the message — "Undo", "View", "Retry". The label is what
     * the button says; $action is called when it is pressed, and the message goes away with it.
     * The offer lives exactly as long as the message: when it times out or is dismissed, the
     * chance to act goes too, so nothing has to expire server-side.
     */
    public string $actionLabel = '';
    /** @var callable|null */
    public $action = null;

    public function __construct(public string $body, public string $variant = '', public ?int $timeout = null)
    {
    }
}
