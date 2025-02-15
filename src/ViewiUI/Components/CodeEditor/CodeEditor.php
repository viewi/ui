<?php

namespace Viewi\UI\Components\CodeEditor;

use Viewi\Builder\Attributes\ExtendWithJs;
use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\HtmlNode;

#[ExtendWithJs]
class CodeEditor extends BaseComponent
{
    public ?HtmlNode $target = null;
    public ?string $model = null;
    // handled by JS
    protected $_editor = null;

    public function rendered()
    {
        $this->setUpEditor();
    }

    public function destroy()
    {
        $this->destroyEditor();
    }

    public function setUpEditor()
    {
        // override in JS
    }

    public function destroyEditor()
    {
        // override in JS
    }
}
