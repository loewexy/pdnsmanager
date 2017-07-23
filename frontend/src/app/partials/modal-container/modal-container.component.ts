import { Component, OnInit } from '@angular/core';
import { ModalService } from 'app/services/modal/modal.service';
import { ModalOptions } from 'app/interfaces/modal-options';

@Component({
    selector: 'app-modal-container',
    templateUrl: './modal-container.component.html',
    styleUrls: ['./modal-container.component.scss']
})
export class ModalContainerComponent implements OnInit {

    private options: ModalOptions = {
        heading: '',
        body: '',
        acceptText: '',
        dismisText: '',
        acceptClass: 'primary'
    };

    private show: boolean = false;
    private animate: boolean = false;

    private currentResolve: Function;
    private currentReject: Function;

    constructor(private modalService: ModalService) { }

    /**
     * Registers this ModalContainerComponent to the ModalService.
     */
    ngOnInit() {
        this.modalService.registerModalContainer(this);
    }

    /**
     * Receives a modal options component and sets the internal config
     * accordingly. Also the modal dialog is shown with correct animation.
     */
    showMessage(options: ModalOptions): Promise<void> {
        this.options = options;

        this.show = true;
        window.setTimeout(() => this.animate = true, 50);

        return new Promise<void>((resolve, reject) => {
            this.currentResolve = resolve;
            this.currentReject = reject;
        });
    }

    /**
     * Hides the modal dialog with correct animation.
     */
    hideModal() {
        this.animate = false;
        window.setTimeout(() => this.show = false, 50);
    }

    /**
     * Handler if the user triggers a dismis action.
     */
    onDismis() {
        this.hideModal();
        if (this.currentReject) {
            this.currentReject();
            this.currentResolve = null;
            this.currentReject = null;
        }
    }

    /**
     * Handler if the user triggers a accept action.
     */
    onAccept() {
        this.hideModal();
        if (this.currentResolve) {
            this.currentResolve();
            this.currentResolve = null;
            this.currentReject = null;
        }
    }

}
