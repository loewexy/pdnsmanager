export class DomainApitype {

    public id = 0;

    public name = '';

    public type = '';

    public master: string = null;

    public records = 0;

    constructor(init: Object) {
        Object.assign(this, init);
    }
}
