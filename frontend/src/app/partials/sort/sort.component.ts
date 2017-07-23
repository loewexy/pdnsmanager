import { Component, Input, Output, EventEmitter } from '@angular/core';

import { SortEvent } from 'app/interfaces/sort-event';

@Component({
    selector: 'app-sort',
    templateUrl: './sort.component.html',
    styleUrls: ['./sort.component.scss']
})
export class SortComponent {

    @Output() sort = new EventEmitter<SortEvent>();

    @Input() field: string;
    private order: number = 0;

    constructor() { }

    /**
     * Resets the sort order for this field. No SortEvent is emitted.
     */
    public reset() {
        this.order = 0;
    }

    /**
     * Resets the sort order for this field, except if this field is the one
     * provided as parameter.
     *
     * @param field The fieldname not to reset
     */
    public resetIfNotField(field: string) {
        if (this.field !== field) {
            this.reset();
        }
    }

    /**
     * Cycles between the three sort states possible. Emits a SortEvent.
     */
    toggle() {
        if (this.order === 0) {
            this.order = 1;
        } else if (this.order === 1) {
            this.order = -1;
        } else if (this.order === -1) {
            this.order = 0;
        }

        this.sort.emit({
            field: this.field,
            order: this.order
        });
    }

}
