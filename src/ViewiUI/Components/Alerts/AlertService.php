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

    /**
     * A message with something to do about it: `$alerts->action('success', 'Moved 12 links.',
     * 'Undo', fn() => $this->undo())`. Give it a timeout — an offer that never expires is a
     * promise the page cannot keep once the person has moved on.
     */
    public function action(string $variant, string $body, string $actionLabel, callable $action, ?int $timeout = null)
    {
        $message = new MessageModel($body, $variant, $timeout);
        $message->id = 'alert' . (++$this->idGenerator);
        $message->actionLabel = $actionLabel;
        $message->action = $action;
        $this->messages = [...$this->messages, $message];
    }

    public function remove(MessageModel $message)
    {
        // array_values: array_filter keeps the keys, and the browser then holds an OBJECT - the next
        // `[...$this->messages, $new]` threw \"not iterable\" (2026-09-22).
        $this->messages = array_values(array_filter($this->messages, fn(MessageModel $m) => $m && $m->id !== $message->id));
    }
}
