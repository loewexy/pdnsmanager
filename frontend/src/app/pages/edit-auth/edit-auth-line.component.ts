import { RecordsOperation } from './../../operations/records.operations';
import { StateService } from './../../services/state.service';
import { DomainApitype } from './../../apitypes/Domain.apitype';
import { FormControl, FormBuilder, Validators } from '@angular/forms';
import { RecordApitype } from './../../apitypes/Record.apitype';
import { Component, OnInit, Input, OnChanges, SimpleChanges, EventEmitter, Output } from '@angular/core';

@Component({
    // tslint:disable-next-line:component-selector
    selector: '[app-edit-auth-line]',
    templateUrl: './edit-auth-line.component.html'
})
export class EditAuthLineComponent implements OnInit, OnChanges {

    @Input() entry: RecordApitype;
    @Input() domain: DomainApitype;

    @Output() recordUpdated = new EventEmitter<void>();

    public editMode = false;

    public inputName: FormControl;
    public inputType: FormControl;
    public inputContent: FormControl;
    public inputPriority: FormControl;
    public inputTtl: FormControl;

    constructor(private fb: FormBuilder, public gs: StateService, private records: RecordsOperation) {
        this.setupFormControls();
    }

    ngOnInit(): void {
    }

    ngOnChanges(changes: SimpleChanges): void {
        this.editMode = false;

        this.inputName.reset(this.recordPart());
        this.inputType.reset(this.entry.type);
        this.inputContent.reset(this.entry.content);
        this.inputPriority.reset(this.entry.priority);
        this.inputTtl.reset(this.entry.ttl);
    }

    public async setupFormControls() {
        this.inputName = this.fb.control('');
        this.inputType = this.fb.control('');
        this.inputContent = this.fb.control('');
        this.inputPriority = this.fb.control('', [Validators.required, Validators.pattern(/^[0-9]+$/)]);
        this.inputTtl = this.fb.control('', [Validators.required, Validators.pattern(/^[0-9]+$/)]);
    }

    public async onEditClick() {
        this.editMode = true;
    }

    public domainPart(): string {
        return '.' + this.domain.name;
    }

    public recordPart(): string {
        const pos = this.entry.name.lastIndexOf(this.domain.name);
        return this.entry.name.substr(0, pos).replace(/\.$/, '');
    }

    public fullName(): string {
        if (this.inputName.value !== '') {
            return this.inputName.value + '.' + this.domain.name;
        } else {
            return this.domain.name;
        }
    }

    public async onSave() {
        await this.records.updateRecord(this.entry.id, this.fullName(),
            this.inputType.value, this.inputContent.value, this.inputPriority.value, this.inputTtl.value);

        this.editMode = false;
        this.recordUpdated.emit();
    }
}
