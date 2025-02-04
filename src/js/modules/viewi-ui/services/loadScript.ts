const includedScripts: {
    [key: string]: {
        loaded: boolean,
        queue: Function[]
    }
} = {};

export type HTMLIFrameElementForScripts = HTMLIFrameElement & { _includedScripts: any }

export function loadScript(script: string, callback: Function, iframe?: HTMLIFrameElementForScripts) {
    const currentIncludedScripts = iframe ? (iframe._includedScripts ?? (iframe._includedScripts = {})) : includedScripts;
    if (script in currentIncludedScripts) {
        if (currentIncludedScripts[script].loaded) {
            callback();
        } else {
            currentIncludedScripts[script].queue.push(callback);
        }
        return;
    }
    currentIncludedScripts[script] = {
        loaded: false,
        queue: []
    };
    const currentDocument = iframe ? iframe.contentDocument! : document;
    let scriptEle = currentDocument.createElement("script");

    scriptEle.setAttribute("src", script);
    scriptEle.setAttribute("type", "text/javascript");
    scriptEle.setAttribute("async", "true");

    currentDocument.body.appendChild(scriptEle);

    // success event 
    scriptEle.addEventListener("load", () => {
        currentIncludedScripts[script].loaded = true;
        callback();
        const queue = currentIncludedScripts[script].queue;
        for (let i = 0; i < queue.length; i++) {
            queue[i]();
        }
        currentIncludedScripts[script].queue = [];
    });
    // error event
    scriptEle.addEventListener("error", (event) => {
        console.error("Error on loading script", event);
    });
}