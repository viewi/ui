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
    public function message(string $variant, string $body, ?int $timeout = null)
    {
        $message = new MessageModel($body, $variant, $timeout);
        $message->id = 'alert' . (++$this->idGenerator);
        $this->messages = [...$this->messages, $message];
    }

    public function success(string $message, ?int $timeout = null)
    {
        $this->message('success', $message, $timeout);
    }

    public function error(string $message, ?int $timeout = null)
    {
        $this->message('error', $message, $timeout);
    }

    public function warning(string $message, ?int $timeout = null)
    {
        $this->message('warning', $message, $timeout);
    }

    public function info(string $message, ?int $timeout = null)
    {
        $this->message('info', $message, $timeout);
    }

    public function remove(MessageModel $message)
    {
        $this->messages = array_filter($this->messages, fn(MessageModel $m) => $m && $m->id !== $message->id);
    }
}
