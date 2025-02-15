import { CKEditor } from "../../../app/main/components/Viewi/UI/Components/CKEditor/CKEditor";
import { loadScript } from "../services/loadScript";
declare var CKEDITOR: any;
declare var ClassicEditor: any;

CKEditor.prototype.setUpEditor = function (this: CKEditor) {
    const $this = this;
    // https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/ckeditor.js
    // https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js
    loadScript('https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js', function () {
        (ClassicEditor || CKEDITOR?.ClassicEditor)
            .create($this.textarea)
            .then(editor => {
                if (CKEditor.fileAdapter) {
                    editor.plugins.get('FileRepository').createUploadAdapter = (loader: any) => {
                        return CKEditor.fileAdapter(loader);
                    };
                }
                $this.editor = editor;
                editor.model.document.on('change:data', function () {
                    const data = editor.getData();
                    $this.emitEvent('model', data);
                    $this.emitEvent('change', data);
                });
            })
            .catch(error => {
                console.error(error);
            });
    });
}

CKEditor.prototype.destroyEditor = function (this: CKEditor) {
    if (this.editor !== null) {
        (this.editor as any).destroy();
        this.editor = null;
    }
}

export { CKEditor }