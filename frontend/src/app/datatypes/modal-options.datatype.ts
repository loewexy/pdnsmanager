
export class ModalOptionsDatatype {

    public heading: '';

    public body: '';

    public acceptText: '';

    public dismisText: '';

    public acceptClass: 'primary';

    constructor(init: {
        heading: string
        body: string
        acceptText: string
        dismisText: string,
        acceptClass?: string
    }) {
        Object.assign(this, init);
    }
}
