import { Injectable } from '@angular/core';

import { ModalContainerComponent } from 'app/partials/modal-container/modal-container.component';
import { ModalOptions } from 'app/interfaces/modal-options';

@Injectable()
export class ModalService {

    container: ModalContainerComponent;

    constructor() { }

    /**
     * Registers a ModalContainerComponent as output for the service
     *
     * @param container ModalContainerComponent to use
     */
    registerModalContainer(container: ModalContainerComponent) {
        this.container = container;
    }

    /**
     * Forwards modal options to a ModalContainerComponent to show them there.
     *
     * @param options   ModalOptions to use
     */
    showMessage(options: ModalOptions) {
        if (this.container) {
            return this.container.showMessage(options);
        }
    }
}
