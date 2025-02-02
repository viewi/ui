<?php

namespace Viewi\UI\Components\Buttons;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;

class ActionButton extends BaseComponent
{
    const STATE_PENDING = 0;
    const STATE_PROCESSING = 1;
    const STATE_SUCCESS = 2;
    const STATE_ERROR = 3;

    public int $state = 0;
    public string $text = 'Save';
    public string $textProcessing = 'Saving..';
    public string $textFailed = 'Failed';
    public string $textSuccess = 'Saved';

    public function onClick(DomEvent $event)
    {
        $this->emitEvent('click', $event);
    }

    public function isPending()
    {
        return $this->state === self::STATE_PENDING;
    }

    public function isProcessing()
    {
        return $this->state === self::STATE_PROCESSING;
    }

    public function isSuccess()
    {
        return $this->state === self::STATE_SUCCESS;
    }    
    
    public function isError()
    {
        return $this->state === self::STATE_ERROR;
    }
}
