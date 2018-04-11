import { DomainApitype } from './../../apitypes/Domain.apitype';
import { SoaApitype } from './../../apitypes/Soa.apitype';
import { DomainsOperation } from './../../operations/domains.operations';
import { FormGroup, FormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute, ParamMap } from '@angular/router';
import { Component, OnInit } from '@angular/core';

@Component({
    selector: 'app-edit-auth',
    templateUrl: './edit-auth.component.html',
    styleUrls: ['./edit-auth.component.scss']
})
export class EditAuthComponent implements OnInit {
    public soaForm: FormGroup;

    public type = '';

    public domainName = '';

    public domainId = 0;

    constructor(private route: ActivatedRoute, private fb: FormBuilder, private domains: DomainsOperation) { }

    ngOnInit() {
        this.createForm();

        this.route.data.subscribe((data) => this.type = data.type);

        this.route.paramMap.subscribe((params) => this.initControl(params));
    }

    private async initControl(params: ParamMap) {
        this.domainId = +params.get('domainId');

        this.domains.getSingle(this.domainId).then((domain: DomainApitype) => {
            this.domainName = domain.name;
        });

        this.domains.getSoa(this.domainId).then((soa: SoaApitype) => {
            this.soaForm.reset({
                primary: soa.primary,
                email: soa.email,
                refresh: soa.refresh,
                retry: soa.retry,
                expire: soa.expire,
                ttl: soa.ttl,
                serial: soa.serial
            });
        });
    }

    private async updateSerial() {
        const soa = await this.domains.getSoa(this.domainId);
        if (soa !== false) {
            this.soaForm.controls['serial'].reset(soa.serial);
        }
    }

    private createForm() {
        this.soaForm = this.fb.group({
            primary: ['', Validators.required],
            email: ['', Validators.email],
            refresh: ['', [Validators.required, Validators.pattern(/^[0-9]+$/)]],
            retry: ['', [Validators.required, Validators.pattern(/^[0-9]+$/)]],
            expire: ['', [Validators.required, Validators.pattern(/^[0-9]+$/)]],
            ttl: ['', [Validators.required, Validators.pattern(/^[0-9]+$/)]],
            serial: ['']
        });
    }

    public async onSubmit() {
        const v = this.soaForm.value;
        await this.domains.setSoa(this.domainId, v.primary, v.email, v.refresh, v.retry, v.expire, v.ttl);
        this.soaForm.markAsPristine();
        await this.updateSerial();
    }

}
