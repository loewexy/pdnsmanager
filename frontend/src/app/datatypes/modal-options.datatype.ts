
export class ModalOptionsDatatype {

    public heading: '';

    public body: '';

    public acceptText: '';

    public dismisText: '';

    public acceptClass: 'primary';

    constructor(init: Object) {
        Object.assign(this, init);
    }
}
