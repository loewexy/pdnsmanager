import { PagingApitype } from './Paging.apitype';

export class ListApitype<T> {

    public paging: PagingApitype;

    public results: T[] = [];

    constructor(init: Object) {
        Object.assign(this, init);
    }
}
