<?php


namespace Viewi\UI\Components\CKEditor;

use Viewi\Components\DOM\DomFile;

abstract class CKEditorUploader
{
    abstract function uploadFile(DomFile $file, CKEUploadRequest $request);

    public function upload()
    {
        $request = new CKEUploadRequest();
        <<<'javascript'
        return this.loader.file.then(file => {
            return new Promise((resolve, reject) => {
                request.onSuccess = resolve;
                request.onError = reject;
                this.uploadFile(file, request);
            });
        });
        javascript;
    }

    abstract function abort();
}
