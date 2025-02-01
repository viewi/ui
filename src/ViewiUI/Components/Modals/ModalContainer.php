<?php

namespace Viewi\UI\Components\Modals;

use Viewi\Components\BaseComponent;

class ModalContainer extends BaseComponent
{
    public function __construct(public ModalService $modalService) {}

    public function confirm(ModalModel $modal)
    {
        $modal->show = false;
        if ($modal->onConfirm) {
            ($modal->onConfirm)();
        }
        $this->modalService->remove($modal);
    }

    public function cancel(ModalModel $modal)
    {
        $modal->show = false;
        if ($modal->onCancel) {
            ($modal->onCancel)();
        }
        $this->modalService->remove($modal);
    }
}
