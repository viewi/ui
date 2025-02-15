
import { CodeEditor } from "../../../app/main/components/Viewi/UI/Components/CodeEditor/CodeEditor";
import { HTMLIFrameElementForScripts, loadScript } from "../services/loadScript";

CodeEditor.prototype.setUpEditor = function (this: CodeEditor) {
    const $this = this;
    const iframe = $this.target as any as HTMLIFrameElementForScripts;
    const targetDiv = iframe.contentDocument!.createElement('div');
    targetDiv.style.height = "100%";
    iframe.contentDocument!.body.appendChild(targetDiv);
    loadScript('https://cdn.jsdelivr.net/npm/@monaco-editor/loader@1.4.0/lib/umd/monaco-loader.min.js', function () {
        (iframe.contentWindow as any).monaco_loader.init().then(function (monaco) {
            const instance = monaco.editor.create(targetDiv, {
                value: $this.model,
                language: 'html',
                automaticLayout: true,
                padding: { top: 5, right: 5, bottom: 5, left: 5 },
                overviewRulerLanes: 0,
                overviewRulerBorder: false,
                theme: 'vs-dark',
            });
            $this._editor = instance;
            instance.onDidChangeModelContent(() => {
                const updatedSourceCode = instance.getValue();
                $this.emitEvent('model', updatedSourceCode);
                $this.emitEvent('change', updatedSourceCode);
            });
        });
    }, iframe);
}

CodeEditor.prototype.destroyEditor = function (this: CodeEditor) {
    if (this._editor !== null) {
        (this._editor as any).dispose();
        this._editor = null;
    }
}

export { CodeEditor }