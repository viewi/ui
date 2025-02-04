<?php


namespace Viewi\UI\Components\CKEditor;

class CKEUploadRequest
{
    public $onSuccess = null;
    public $onError = null;

    public function success(string $path)
    {
        ($this->onSuccess)(['default' => $path]);
    }

    public function error(string $path)
    {
        ($this->onError)($path);
    }
}
