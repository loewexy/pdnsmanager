export class RecordApitype {

    public id = 0;

    public name = '';

    public type = '';

    public content = '';

    public priority = 0;

    public ttl = 0;

    public domain = 0;

    constructor(init: Object) {
        Object.assign(this, init);
    }
}
