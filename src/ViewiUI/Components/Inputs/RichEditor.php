<?php

namespace Viewi\UI\Components\Inputs;

use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\DomEvent;
use Viewi\Components\DOM\HtmlNode;

class RichEditor extends BaseComponent
{
    public ?string $type = null;
    public ?string $placeholder = null;
    public ?string $label = null;
    public ?string $hint = null;
    public ?string $autocomplete = null;
    public ?string $inputClass = null;
    public ?string $wrapperClass = null;
    public ?string $id = null;
    public ?string $name = null;
    public ?string $model = null;
    public ?HtmlNode $input = null;
    public ?int $rows = null;
    public ?int $cols = null;
    public $isInvalid = false;
    public bool $inset = false;
    public bool $codeEditor = false;

    public function __construct() {}

    public function onInput(DomEvent $event)
    {
        $this->onContentChange($this->type === 'number' && !$event->target->value ? null : $event->target->value);
    }

    public function onContentChange($content)
    {
        $this->emitEvent('model', $content);
    }
}
