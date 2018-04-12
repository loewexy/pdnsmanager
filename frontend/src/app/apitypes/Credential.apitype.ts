export class CredentialApitype {

    public id = 0;

    public description = '';

    public type = '';

    public key: string = null;

    constructor(init: Object) {
        Object.assign(this, init);
    }
}
