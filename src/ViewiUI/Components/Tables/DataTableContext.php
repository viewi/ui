<?php

namespace Viewi\UI\Components\Tables;

class DataTableContext
{
    private $_onUpdate = null;
    private array $_props = [];
    private array $_events = /* @jsobject */ [];

    public function passProps(array $props)
    {
        $this->_props = $props;
        if ($this->_onUpdate !== null) {
            ($this->_onUpdate)($props);
        }
    }

    public function onUpdate($onUpdate)
    {
        $this->_onUpdate = $onUpdate;
        $this->passProps($this->_props);
    }

    function on(string $eventName, $callback)
    {
        $this->_events[$eventName] = $callback;
    }

    function emitEvent(string $eventName, $event = null)
    {
        if (isset($this->_events[$eventName])) {
            ($this->_events[$eventName])($event);
        }
    }
}
