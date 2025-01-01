<?php

namespace Viewi\UI\Components\Tabs;

class TabsContext
{
    /**
     * 
     * @var TabItem[]
     */
    public array $tabs = [];

    public function addTab(TabItem $tab)
    {
        $this->tabs = [...$this->tabs, $tab];
    }

    public function selectTab(TabItem $target)
    {
        foreach ($this->tabs as $tabItem) {
            $tabItem->active = false;
        }
        $target->active = true;
    }
}
