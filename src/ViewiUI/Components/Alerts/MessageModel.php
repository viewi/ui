<?php

namespace Viewi\UI\Components\Alerts;

class MessageModel
{
    public bool $show = true;
    public ?string $id = null;

    public function __construct(public string $body, public string $variant = '', public ?int $timeout = null)
    {
    }
}
