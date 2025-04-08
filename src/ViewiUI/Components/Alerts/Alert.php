<?php

namespace Viewi\UI\Components\Alerts;

use Viewi\Components\BaseComponent;

class Alert extends BaseComponent
{
    public ?string $id = null;
    public string $body;
    public string $variant = '';
    public ?int $timeout = null;
    public bool $show = true;
    public bool $dismissible = true;
    public string $icon = '';

    public function variantClass()
    {
        switch ($this->variant) {
            case 'success': {
                    $this->icon = 'bi-check-circle-fill';
                    return 'alert-success';
                }
            case 'error': {
                    $this->icon = 'bi-exclamation-triangle';
                    return 'alert-danger';
                }
            case 'warning': {
                    $this->icon = 'bi-exclamation-triangle';
                    return 'alert-warning';
                }
            case 'info':
            case '': {
                    $this->icon = 'bi-info-circle';
                    return 'alert-primary';
                }
            default:
                return $this->variant;
        }
    }

    public function mounted()
    {
        if ($this->timeout !== null) {
            <<<'javascript'
            setTimeout(() => $this.onClose(), $this.timeout);
            javascript;
        }
    }

    public function onClose()
    {
        if ($this->show) {
            $this->show = false;
            $this->emitEvent('dismiss', true);
        }
    }
}
