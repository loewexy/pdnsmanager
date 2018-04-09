import { DomainsOperation } from './../../operations/domains.operations';
import { ActivatedRoute, ParamMap } from '@angular/router';
import { FormGroup, Validators, FormBuilder } from '@angular/forms';
import { Component, OnInit } from '@angular/core';

@Component({
    selector: 'app-edit-slave',
    templateUrl: './edit-slave.component.html',
    styleUrls: ['./edit-slave.component.scss']
})
export class EditSlaveComponent implements OnInit {

    public slaveForm: FormGroup;

    constructor(private fb: FormBuilder, private route: ActivatedRoute, private domains: DomainsOperation) { }

    public domainName = '';
    public domainId = 0;

    ngOnInit() {
        this.createForm();

        this.route.paramMap.subscribe((params) => this.initControl(params));
    }

    private async initControl(params: ParamMap) {
        const domain = await this.domains.getSingle(+params.get('domainId'));

        this.domainName = domain.name;
        this.domainId = domain.id;

        this.slaveForm.reset({ master: domain.master });
    }

    private createForm() {
        this.slaveForm = this.fb.group({
            master: ['', Validators.required]
        });
    }

    public async onSubmit() {
        await this.domains.updateMaster(this.domainId, this.slaveForm.value.master);

        this.slaveForm.markAsPristine();
    }
}
