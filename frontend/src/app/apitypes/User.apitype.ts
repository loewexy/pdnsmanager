
export class UserApitype {

    public id: number = null;

    public name: string = null;

    public type: string = null;

    public native: boolean = null;

    public password: string = null;

    constructor(init: Object) {
        Object.assign(this, init);
    }
}
