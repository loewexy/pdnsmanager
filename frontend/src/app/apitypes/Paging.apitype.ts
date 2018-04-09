export class PagingApitype {

    public page = 1;

    public total = 1;

    public pagesize: number = null;

    constructor(init: Object) {
        Object.assign(this, init);
    }
}
