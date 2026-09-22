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
        // array_values: array_filter keeps the keys, and the browser then holds an OBJECT - the next
        // `[...$this->dialogs, $new]` threw \"not iterable\" (2026-09-22).
        $this->dialogs = array_values(array_filter($this->dialogs, fn(ModalModel $m) => $m && $m->id !== $message->id));
    }
}
