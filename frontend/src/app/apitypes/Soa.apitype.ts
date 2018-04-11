export class SoaApitype {

    public primary = '';

    public email = '';

    public refresh = 0;

    public retry = 0;

    public expire = 0;

    public ttl = 0;

    public serial = 0;

    constructor(init: Object) {
        Object.assign(this, init);
    }
}
