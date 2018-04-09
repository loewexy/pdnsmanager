import { Component, OnInit, Input, forwardRef } from '@angular/core';
import { ControlValueAccessor, NG_VALUE_ACCESSOR } from '@angular/forms';

@Component({
    selector: 'app-select',
    templateUrl: './select.component.html',
    styleUrls: ['./select.component.scss'],
    providers: [{
        provide: NG_VALUE_ACCESSOR,
        multi: true,
        useExisting: forwardRef(() => SelectComponent)
    }]
})
export class SelectComponent implements OnInit, ControlValueAccessor {

    @Input() options: Array<string> = [];
    @Input() emptyText = 'Choose..';
    @Input() multiple = false;

    public open = false;

    public selections: Array<string> = [];

    public enabled = true;

    private onChange = (_: any) => { };
    private onTouch = () => { };

    constructor() { }

    ngOnInit() {
    }

    public toggleOpen() {
        this.open = !this.open;
        this.onTouch();
    }

    public onClick(item) {
        if (this.multiple !== false) {
            if (this.selections.includes(item)) {
                this.selections = this.selections.filter((i) => i !== item);
            } else {
                this.selections.push(item);
            }
        } else {
            this.selections = [item];
            this.open = false;
        }

        this.emitValueChange();
    }

    public emitValueChange() {
        if (this.multiple !== false) {
            this.onChange(this.selections.length === 0 ? null : this.selections);
        } else {
            this.onChange(this.selections.length === 0 ? null : this.selections[0]);
        }
    }

    public isActive(item) {
        return this.selections.includes(item);
    }

    public buttonText() {
        if (this.selections.length === 0) {
            return this.emptyText;
        } else {
            return this.selections.join(',');
        }
    }

    public reset() {
        this.selections = [];
        this.open = false;
        this.emitValueChange();
    }

    public writeValue(obj: any): void {
        console.log('input obj ' + JSON.stringify(obj));
        console.log(obj);
        if (obj === null) {
            this.selections = [];
        } else if (obj instanceof Array) {
            this.selections = obj;
        } else {
            this.selections = [obj.toString()];
        }
    }

    public registerOnChange(fn: any): void {
        this.onChange = fn;
    }

    public registerOnTouched(fn: any): void {
        this.onTouch = fn;
    }

    public setDisabledState(isDisabled: boolean): void {
        this.enabled = !isDisabled;
    }

}
