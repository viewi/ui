import { ExpandTransition } from "./../../../app/main/components/Viewi/UI/Components/Transition/ExpandTransition";
function clear(target: HTMLElement, show: boolean) {
    setTimeout(() => {
        target.classList.remove('collapsing');
        target.style.removeProperty('height');
        target.classList.add('collapse');
        show && target.classList.add('show');
    }, 500);
}

function show(target: HTMLElement, show: boolean) {
    target.classList.remove('collapse');
    target.classList.remove('show');
    target.classList.add('collapsing');
    target.style.height = target.scrollHeight + 'px';
    clear(target, show);
}

function hide(target: HTMLElement, show: boolean) {
    target.style.height = target.getBoundingClientRect().height + 'px';
    target.offsetHeight; // reflow
    target.classList.remove('collapse');
    target.classList.remove('show');
    target.classList.add('collapsing');
    target.style.height = '0';
    clear(target, show);
}

ExpandTransition.prototype.onExpandedChange = function (this: ExpandTransition) {
    if (this._element && this._element.nextElementSibling) {
        const target = this._element.nextElementSibling as HTMLElement;
        if (this.expanded) {
            show(target, this.expanded);
        } else {
            hide(target, this.expanded);
        }
    }
}

ExpandTransition.prototype.rendered = function (this: ExpandTransition) {
    if (this._element && this._element.nextElementSibling) {
        const target = this._element.nextElementSibling as HTMLElement;
        target.classList.add('collapse');
        this.expanded && target.classList.add('show');
    }
}

export { ExpandTransition }