import { PagingApitype } from './../../apitypes/Paging.apitype';
import { Component, Input, EventEmitter, Output } from '@angular/core';

@Component({
    selector: 'app-pagesize',
    templateUrl: './pagesize.component.html',
    styleUrls: ['./pagesize.component.scss']
})
export class PagesizeComponent {

    @Input() pagesizes: Array<number>;

    @Input() currentPagesize: number;

    @Output() pagesizeChange = new EventEmitter<number>();

    constructor() {
    }

    public newPagesize(pagesize: number) {
        this.pagesizeChange.emit(pagesize);
    }
}
