import { PagingApitype } from './../../apitypes/Paging.apitype';
import { StateService } from './../../services/state.service';
import { PermissionApitype } from './../../apitypes/Permission.apitype';
import { DomainsOperation } from './../../operations/domains.operations';
import { UsersOperation } from './../../operations/users.operations';
import { ModalService } from './../../services/modal.service';
import { ActivatedRoute, ParamMap, Router } from '@angular/router';
import { FormGroup, Validators, FormBuilder } from '@angular/forms';
import { Component, OnInit } from '@angular/core';
import { ModalOptionsDatatype } from '../../datatypes/modal-options.datatype';
import { PasswordValidationUtil } from '../../utils/password-validation.util';

@Component({
    selector: 'app-create-user',
    templateUrl: './edit-user.component.html',
    styleUrls: ['./edit-user.component.scss']
})
export class EditUserComponent implements OnInit {

    public userForm: FormGroup;

    public permissions: PermissionApitype[] = [];

    public pageRequested = 1;
    public pagingInfo = new PagingApitype({});

    public isNative = false;
    public username = '';
    public userId = 0;

    constructor(private fb: FormBuilder, private route: ActivatedRoute, private users: UsersOperation,
        private router: Router, private modal: ModalService, public domains: DomainsOperation, public gs: StateService) { }

    ngOnInit() {
        this.createForm();

        this.route.paramMap.subscribe((params) => this.initControl(params));
    }

    private async initControl(params: ParamMap) {
        this.userId = +params.get('userId');

        this.loadPermissions();

        const user = await this.users.getSingle(this.userId);

        this.username = user.name;
        this.isNative = user.native;

        this.userForm.reset({
            name: user.name,
            type: user.type
        });
    }

    private createForm() {
        this.userForm = this.fb.group({
            name: ['', Validators.required],
            type: ['user'],
            password: [''],
            password2: ['']
        }, { validator: PasswordValidationUtil.matchPassword });
    }

    public async onSubmit() {
        try {
            const v = this.userForm.value;

            if (this.isNative) {
                const name = this.userForm.controls['name'].dirty ? v.name : null;
                const password = v.password !== '' ? v.password : null;
                await this.users.updateUser(this.userId, name, v.type, password);
            } else {
                await this.users.updateUser(this.userId, null, v.type);
            }

            this.userForm.reset({
                name: v.name,
                type: v.type,
                password: '',
                password2: ''
            });
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

    public async loadPermissions() {
        const res = await this.users.getPermissions(this.pageRequested, this.gs.pageSize, this.userId);
        this.permissions = res.results;
        this.pagingInfo = res.paging;
        if (res.paging.total < this.pageRequested && res.paging.total > 1) {
            this.pageRequested = Math.max(1, res.paging.total);
            await this.loadPermissions();
        }
    }

    public async onRemovePermission(permission: PermissionApitype) {
        await this.users.removePermission(this.userId, permission.domainId);
        await this.loadPermissions();
    }

    public async addPermissionFor(domainId: number) {
        await this.users.addPermission(this.userId, domainId);
        await this.loadPermissions();
    }

    public async onPagesizeChange(pagesize: number) {
        this.pageRequested = 1;
        this.gs.pageSize = pagesize;
        await this.loadPermissions();
    }

    public async onPageChange(page: number) {
        this.pageRequested = page;
        await this.loadPermissions();
    }
}
