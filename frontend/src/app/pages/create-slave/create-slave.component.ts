import { ModalService } from './../../services/modal.service';
import { DomainsOperation } from './../../operations/domains.operations';
import { ActivatedRoute, ParamMap, Router } from '@angular/router';
import { FormGroup, Validators, FormBuilder } from '@angular/forms';
import { Component, OnInit } from '@angular/core';
import { ModalOptionsDatatype } from '../../datatypes/modal-options.datatype';

@Component({
    selector: 'app-create-slave',
    templateUrl: './create-slave.component.html',
    styleUrls: ['./create-slave.component.scss']
})
export class CreateSlaveComponent implements OnInit {

    public slaveForm: FormGroup;

    constructor(private fb: FormBuilder, private route: ActivatedRoute, private domains: DomainsOperation,
        private router: Router, private modal: ModalService) { }

    ngOnInit() {
        this.createForm();
    }

    private createForm() {
        this.slaveForm = this.fb.group({
            name: ['', Validators.required],
            master: ['', Validators.required]
        });
    }

    public async onSubmit() {
        try {
            const v = this.slaveForm.value;

            const newDomain = await this.domains.create(v.name, 'SLAVE', v.master);

            this.router.navigate(['/domains/slave', newDomain.id.toString()]);
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
