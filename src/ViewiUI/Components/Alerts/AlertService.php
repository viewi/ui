<?php

namespace Viewi\UI\Components\Alerts;

use Viewi\DI\Singleton;

#[Singleton]
class AlertService
{
    /**
     * 
     * @var MessageModel[]
     */
    public array $messages = [];
    private int $idGenerator = 0;

    // TODO: until -> dispose auto
    public function message(string $variant, string $body, ?string $title = null, ?int $timeout = null)
    {
        $message = new MessageModel($body, $title, $variant, $timeout);
        $message->id = 'alert' . (++$this->idGenerator);
        $this->messages = [...$this->messages, $message];
    }

    public function success(string $message, ?string $title = null, ?int $timeout = null)
    {
        $this->message('success', $message, $title, $timeout);
    }

    public function error(string $message, ?string $title = null, ?int $timeout = null)
    {
        $this->message('error', $message, $title, $timeout);
    }

    public function warning(string $message, ?string $title = null, ?int $timeout = null)
    {
        $this->message('warning', $message, $title, $timeout);
    }

    public function info(string $message, ?string $title = null, ?int $timeout = null)
    {
        $this->message('info', $message, $title, $timeout);
    }

    public function remove(MessageModel $message)
    {
        $this->messages = array_filter($this->messages, fn(MessageModel $m) => $m && $m->id !== $message->id);
    }
}
