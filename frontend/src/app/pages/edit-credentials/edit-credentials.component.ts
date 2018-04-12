import { CredentialApitype } from './../../apitypes/Credential.apitype';
import { RecordsOperation } from './../../operations/records.operations';
import { CredentialsOperation } from './../../operations/credentials.operations';
import { RecordApitype } from './../../apitypes/Record.apitype';
import { PagingApitype } from './../../apitypes/Paging.apitype';
import { PermissionApitype } from './../../apitypes/Permission.apitype';
import { ModalService } from './../../services/modal.service';
import { ActivatedRoute, ParamMap, Router } from '@angular/router';
import { FormGroup, Validators, FormBuilder } from '@angular/forms';
import { Component, OnInit } from '@angular/core';
import { ModalOptionsDatatype } from '../../datatypes/modal-options.datatype';
import { PasswordValidationUtil } from '../../utils/password-validation.util';

@Component({
    selector: 'app-edit-credentials',
    templateUrl: './edit-credentials.component.html',
    styleUrls: ['./edit-credentials.component.scss']
})
export class EditCredentialsComponent implements OnInit {
    public keyForm: FormGroup;
    public passwordForm: FormGroup;

    public editType = '';
    public editId = 0;

    public keyInvalid = false;

    public credentialList: CredentialApitype[] = [];

    public domainId = 0;
    public recordId = 0;
    public record: RecordApitype = new RecordApitype({});

    constructor(private fb: FormBuilder, private route: ActivatedRoute, private credentials: CredentialsOperation,
        private router: Router, private modal: ModalService, public records: RecordsOperation) { }

    ngOnInit() {
        this.createForm();

        this.route.paramMap.subscribe((params) => this.initControl(params));
    }

    private async initControl(params: ParamMap) {
        this.recordId = +params.get('recordId');
        this.domainId = +params.get('domainId');

        this.loadCredentials();
    }

    private createForm() {
        this.keyForm = this.fb.group({
            description: ['', Validators.required],
            key: ['', Validators.required]
        });

        this.passwordForm = this.fb.group({
            description: ['', Validators.required],
            password: ['', Validators.required],
            password2: ['']
        }, { validator: PasswordValidationUtil.matchPassword });
    }

    public async onSubmit() {
        this.keyInvalid = false;
        try {
            if (this.editId === 0) {
                if (this.editType === 'key') {
                    const v = this.keyForm.value;
                    await this.credentials.createKey(this.recordId, v.description, v.key);
                } else if (this.editType === 'password') {
                    const v = this.passwordForm.value;
                    await this.credentials.createPassword(this.recordId, v.description, v.password);
                }
            } else {
                if (this.editType === 'key') {
                    const v = this.keyForm.value;
                    await this.credentials.updateKey(this.recordId, this.editId, v.description, v.key);
                } else if (this.editType === 'password') {
                    const v = this.passwordForm.value;
                    await this.credentials.updatePassword(this.recordId, this.editId, v.description, v.password);
                }
            }

            this.editId = 0;
            this.editType = '';
            await this.loadCredentials();
        } catch (e) {
            this.keyInvalid = true;
        }
    }

    public async onAddKey() {
        this.editId = 0;
        this.editType = 'key';
        this.keyInvalid = false;

        this.keyForm.reset({
            description: '',
            key: ''
        });
    }

    public async onAddPassword() {
        this.editId = 0;
        this.editType = 'password';
        this.passwordForm.controls['password'].setValidators(Validators.required);

        this.passwordForm.reset({
            description: '',
            password: '',
            password2: ''
        });
    }

    public async onEditClick(credentialId: number) {
        const credential = await this.credentials.getSingle(this.recordId, credentialId);

        if (credential.type === 'key') {
            this.editType = 'key';
            this.editId = credentialId;
            this.keyInvalid = false;
            this.keyForm.reset({
                description: credential.description,
                key: credential.key
            });
        } else if (credential.type === 'password') {
            this.editType = 'password';
            this.editId = credentialId;
            this.passwordForm.controls['password'].clearValidators();
            this.passwordForm.reset({
                description: credential.description,
                password: '',
                password2: ''
            });
        }
    }

    public async loadCredentials() {
        const res = await this.credentials.getList(this.recordId);

        this.credentialList = res.results;
    }

    public async onRemoveCredential(credential: CredentialApitype) {
        try {
            await this.modal.showMessage(new ModalOptionsDatatype({
                heading: 'Confirm deletion',
                body: 'Are you shure you want to delete the credential ' + credential.description + '?',
                acceptText: 'Delete',
                dismisText: 'Cancel',
                acceptClass: 'danger'
            }));
            await this.credentials.delete(this.recordId, credential.id);
            await this.loadCredentials();
        } catch (e) {
        }
    }
}
