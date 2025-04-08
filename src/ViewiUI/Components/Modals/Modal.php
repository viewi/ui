<?php

namespace Viewi\UI\Components\Modals;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\Components\DOM\HtmlNode;

class Modal extends BaseComponent
{
    public ?string $id = null;
    public bool $show = false;
    public bool $closeButton = false;
    public ?string $header = null;
    public string $confirmButtonText = 'Ok';
    public string $confirmButtonClass = 'btn-primary';
    public bool $showConfirm = false;
    public string $cancelButtonText = 'Cancel';
    public bool $showCancel = false;
    public string $title = '';
    public ?string $size = null;
    public ?HtmlNode $body = null;
    public ?string $classList = null;

    public function getSize(): string
    {
        switch ($this->size) {
            case 'sm': {
                    return 'modal-sm';
                }
            case 'lg': {
                    return 'modal-lg';
                }
            case 'xl': {
                    return 'modal-xl';
                }
        }
        return '';
    }

    public function onClose(DomEvent $event)
    {
        $this->emitEvent('close', $event);
        $this->show = false;
    }

    public function onConfirm(DomEvent $event)
    {
        $this->emitEvent('confirm', $event);
        $this->onClose($event);
    }

    public function onCancel(DomEvent $event)
    {
        $this->emitEvent('cancel', $event);
        $this->onClose($event);
    }

    public function getBodyElement()
    {
        return $this->body;
    }
}
