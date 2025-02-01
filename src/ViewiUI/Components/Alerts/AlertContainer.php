<?php

namespace Viewi\UI\Components\Alerts;

use Viewi\Components\BaseComponent;

class AlertContainer extends BaseComponent
{
    public function __construct(public AlertService $messages)
    {
    }

    public function onDismiss(MessageModel $message)
    {
        $message->show = false;
        $this->messages->remove($message);
    }
}
