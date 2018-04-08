
export class SortEventDatatype {

    public field: string;
    public order: number;

    constructor(init: Object) {
        Object.assign(this, init);
    }
}
