<?php


namespace Viewi\UI\Components\CKEditor;

use Viewi\Builder\Attributes\ExtendWithJs;
use Viewi\Components\BaseComponent;
use Viewi\Components\DOM\HtmlNode;

#[ExtendWithJs]
class CKEditor extends BaseComponent
{
    public ?HtmlNode $textarea = null;
    // handled by JS
    protected $editor = null;
    public static $fileAdapter = null;

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
