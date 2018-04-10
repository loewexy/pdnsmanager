import { ModalOptionsDatatype } from './../../datatypes/modal-options.datatype';
import { ModalService } from './../../services/modal.service';
import { DomainsOperation } from './../../operations/domains.operations';
import { ActivatedRoute, ParamMap, Router } from '@angular/router';
import { FormGroup, Validators, FormBuilder, AbstractControl, ValidationErrors } from '@angular/forms';
import { Component, OnInit } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/observable/timer';
import 'rxjs/add/operator/switchMap';
import 'rxjs/add/operator/map';
import { fromPromise } from 'rxjs/observable/fromPromise';

@Component({
    selector: 'app-create-slave',
    templateUrl: './create-auth.component.html',
    styleUrls: ['./create-auth.component.scss']
})
export class CreateAuthComponent implements OnInit {

    public authForm: FormGroup;
    public type: string;

    constructor(private fb: FormBuilder, private route: ActivatedRoute, private domains: DomainsOperation,
        private router: Router, private modal: ModalService) { }

    ngOnInit() {
        this.createForm();

        this.route.data.subscribe((data) => this.type = data.type);
    }

    private createForm() {
        this.authForm = this.fb.group({
            name: ['', Validators.required],
            primary: ['', Validators.required],
            email: ['', Validators.email],
            refresh: ['3600', [Validators.required, Validators.pattern(/^[0-9]+$/)]],
            retry: ['900', [Validators.required, Validators.pattern(/^[0-9]+$/)]],
            expire: ['604800', [Validators.required, Validators.pattern(/^[0-9]+$/)]],
            ttl: ['86400', [Validators.required, Validators.pattern(/^[0-9]+$/)]]
        });
    }

    public async onSubmit() {
        try {
            const v = this.authForm.value;

            const domain = await this.domains.create(v.name, this.type);

            await this.domains.setSoa(domain.id, v.primary, v.email, +v.refresh, +v.retry, +v.expire, +v.ttl);

            this.router.navigate(['/domains/master', domain.id.toString()]);
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
