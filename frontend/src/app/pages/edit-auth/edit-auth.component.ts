import { EditAuthLineComponent } from './edit-auth-line.component';
import { RecordApitype } from './../../apitypes/Record.apitype';
import { StateService } from './../../services/state.service';
import { RecordsOperation } from './../../operations/records.operations';
import { DomainApitype } from './../../apitypes/Domain.apitype';
import { SoaApitype } from './../../apitypes/Soa.apitype';
import { DomainsOperation } from './../../operations/domains.operations';
import { FormGroup, FormBuilder, Validators, FormControl } from '@angular/forms';
import { ActivatedRoute, ParamMap } from '@angular/router';
import { Component, OnInit } from '@angular/core';
import { PagingApitype } from '../../apitypes/Paging.apitype';
import { SortEventDatatype } from '../../datatypes/sort-event.datatype';
import 'rxjs/add/operator/filter';

@Component({
    selector: 'app-edit-auth',
    templateUrl: './edit-auth.component.html',
    styleUrls: ['./edit-auth.component.scss']
})
export class EditAuthComponent implements OnInit {
    public soaForm: FormGroup;

    public type = '';

    public domain: DomainApitype = new DomainApitype({});

    public domainId = 0;

    public pagingInfo = new PagingApitype({});
    public pageRequested = 1;

    public recordList: RecordApitype[] = [];

    public sortField = '';
    public sortOrder = 'asc';

    public queryNameInput: FormControl;
    public queryContentInput: FormControl;
    public typeFilter: FormControl;

    constructor(private route: ActivatedRoute, private fb: FormBuilder, public gs: StateService,
        private domains: DomainsOperation, private records: RecordsOperation) { }

    ngOnInit() {
        this.createForm();

        this.route.data.subscribe((data) => this.type = data.type);

        this.route.paramMap.subscribe((params) => this.initControl(params));
    }

    private async initControl(params: ParamMap) {
        this.domainId = +params.get('domainId');

        this.domains.getSingle(this.domainId).then((domain: DomainApitype) => {
            this.domain = domain;
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

        this.queryNameInput.reset();
        this.queryContentInput.reset();

        // this triggers also a reload of the records, therefore this function is ommited here
        this.typeFilter.reset();
    }

    public async updateSerial() {
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

        this.queryNameInput = new FormControl('');
        this.queryNameInput.valueChanges.filter((d) => d !== null).debounceTime(500).subscribe(() => this.loadRecords());

        this.queryContentInput = new FormControl('');
        this.queryContentInput.valueChanges.filter((d) => d !== null).debounceTime(500).subscribe(() => this.loadRecords());

        this.typeFilter = new FormControl(null);
        this.typeFilter.valueChanges.subscribe(() => this.loadRecords());
    }

    public async onSoaSubmit() {
        const v = this.soaForm.value;
        await this.domains.setSoa(this.domainId, v.primary, v.email, v.refresh, v.retry, v.expire, v.ttl);
        this.soaForm.markAsPristine();
        await this.updateSerial();
    }

    public async loadRecords() {
        const sortStr = this.sortField !== '' ? this.sortField + '-' + this.sortOrder : null;
        const queryName = this.queryNameInput.value !== '' ? this.queryNameInput.value : null;
        const queryContent = this.queryContentInput.value !== '' ? this.queryContentInput.value : null;
        const typeFilter = this.typeFilter.value;

        const res = await this.records.getListForDomain(this.domainId, this.pageRequested,
            this.gs.pageSize, queryName, typeFilter, queryContent, sortStr);

        if (res.paging.total < this.pageRequested) {
            this.pageRequested = Math.max(1, res.paging.total);
            await this.loadRecords();
        } else {
            this.pagingInfo = res.paging;
            this.recordList = res.results;
        }
    }

    public async onPageChange(newPage: number) {
        this.pageRequested = newPage;
        await this.loadRecords();
    }

    public async onPagesizeChange(pagesize: number) {
        this.gs.pageSize = pagesize;
        this.pageRequested = 1;
        await this.loadRecords();
    }

    public async onSortEvent(sortEvent: SortEventDatatype) {
        if (sortEvent.order === 0) {
            this.sortField = '';
            this.sortOrder = 'asc';
        } else {
            this.sortField = sortEvent.field;
            this.sortOrder = sortEvent.order === 1 ? 'asc' : 'desc';
        }

        await this.loadRecords();
    }

}
