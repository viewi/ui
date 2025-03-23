<?php

namespace Viewi\UI\Components\Accordion;

class AccordionContext
{
    /**
     * 
     * @var AccordionTab[]
     */
    private array $_tabs = [];
    public bool $multiple = false;

    public function add(AccordionTab $tab)
    {
        $this->_tabs[] = $tab;
    }

    public function collapse(AccordionTab $tab)
    {

        $tab->open = !$tab->open;
        if (!$this->multiple && $tab->open) {
            foreach ($this->_tabs as $otherTab) {
                if ($otherTab !== $tab) {
                    $otherTab->open = false;
                }
            }
        }
    }
}
