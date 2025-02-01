<?php

namespace Viewi\UI\Components\Modals;

class ModalModel
{
    public bool $show = true;
    public ?string $id = null;

    public function __construct(public string $title, public $onConfirm = null, public $onCancel = null) {}
}
