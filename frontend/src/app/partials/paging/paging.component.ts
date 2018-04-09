import { PagingApitype } from './../../apitypes/Paging.apitype';
import { Component, Input, EventEmitter, Output } from '@angular/core';

@Component({
    selector: 'app-paging',
    templateUrl: './paging.component.html',
    styleUrls: ['./paging.component.scss']
})
export class PagingComponent {

    @Input() pagingInfo: PagingApitype;

    @Input() pageWidth = 2;

    @Output() pageChange = new EventEmitter<number>();

    constructor() { }

    public createNumbers(): Array<number> {
        const min = Math.max(1, this.pagingInfo.page - this.pageWidth);
        const max = Math.min(this.pagingInfo.total, this.pagingInfo.page + this.pageWidth);

        const pages = [];
        for (let i = min; i <= max; i++) {
            pages.push(i);
        }
        return pages;
    }

    public truncatesLeft(): boolean {
        return this.pagingInfo.page - this.pageWidth > 1;
    }

    public truncatesRight(): boolean {
        return this.pagingInfo.page + this.pageWidth < this.pagingInfo.total;
    }

    public previous(): void {
        this.pageChange.emit(this.pagingInfo.page - 1);
    }

    public next(): void {
        this.pageChange.emit(this.pagingInfo.page + 1);
    }

    public newPage(page: number) {
        this.pageChange.emit(page);
    }
}
