
export class SessionApitype {

    public username: string = null;

    public token: string = null;

    constructor(init: Object) {
        Object.assign(this, init);
    }
}
