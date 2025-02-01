<?php

namespace Viewi\UI\Components\Modals;

use Viewi\DI\Singleton;

#[Singleton]
class ModalService
{
    /**
     * 
     * @var ModalModel[]
     */
    public array $dialogs = [];
    private int $idGenerator = 0;

    public function confirm(string $title, ?callable $onConfirm = null, ?callable $onCancel = null)
    {
        $message = new ModalModel($title, $onConfirm, $onCancel);
        $message->id = 'toast' . (++$this->idGenerator);
        $this->dialogs = [...$this->dialogs, $message];
    }

    public function remove(ModalModel $message)
    {
        $this->dialogs = array_filter($this->dialogs, fn(ModalModel $m) => $m && $m->id !== $message->id);
    }
}
