import { PasswordOperation } from './../../operations/password.operations';
import { PasswordValidationUtil } from './../../utils/password-validation.util';
import { FormGroup, Validators, FormBuilder } from '@angular/forms';
import { Component, OnInit } from '@angular/core';

@Component({
    selector: 'app-password',
    templateUrl: './password.component.html',
    styleUrls: ['./password.component.scss']
})
export class PasswordComponent {

    public passwordForm: FormGroup;

    public changeSuccessfull = false;

    constructor(private fb: FormBuilder, private password: PasswordOperation) {
        this.createForm();
    }

    private createForm() {
        this.passwordForm = this.fb.group({
            password: ['', Validators.required],
            password2: ['', Validators.required]
        }, { validator: PasswordValidationUtil.matchPassword });
    }

    public async onSubmit() {
        this.changeSuccessfull = await this.password.changePassword(this.passwordForm.value.password);
        this.passwordForm.reset();
        setTimeout(() => this.changeSuccessfull = false, 3000);
    }
}
