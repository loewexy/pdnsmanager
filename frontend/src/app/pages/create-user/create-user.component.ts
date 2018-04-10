import { UsersOperation } from './../../operations/users.operations';
import { ModalService } from './../../services/modal.service';
import { ActivatedRoute, ParamMap, Router } from '@angular/router';
import { FormGroup, Validators, FormBuilder } from '@angular/forms';
import { Component, OnInit } from '@angular/core';
import { ModalOptionsDatatype } from '../../datatypes/modal-options.datatype';
import { PasswordValidationUtil } from '../../utils/password-validation.util';

@Component({
    selector: 'app-create-user',
    templateUrl: './create-user.component.html',
    styleUrls: ['./create-user.component.scss']
})
export class CreateUserComponent implements OnInit {

    public userForm: FormGroup;

    constructor(private fb: FormBuilder, private route: ActivatedRoute, private users: UsersOperation,
        private router: Router, private modal: ModalService) { }

    ngOnInit() {
        this.createForm();
    }

    private createForm() {
        this.userForm = this.fb.group({
            name: ['', Validators.required],
            type: ['user', Validators.required],
            password: ['', Validators.required],
            password2: ['']
        }, { validator: PasswordValidationUtil.matchPassword });
    }

    public async onSubmit() {
        try {
            const v = this.userForm.value;

            const newUser = await this.users.create(v.name, v.type, v.password);

            this.router.navigate(['/users', newUser.id.toString()]);
        } catch (e) {
            await this.modal.showMessage(new ModalOptionsDatatype({
                heading: 'Error',
                body: e.message,
                acceptText: 'OK',
                dismisText: '',
                acceptClass: 'warning'
            }));
        }
    }
}
