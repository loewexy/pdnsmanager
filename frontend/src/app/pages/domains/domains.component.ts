import { SortEventDatatype } from './../../datatypes/sort-event.datatype';
import { ModalOptionsDatatype } from './../../datatypes/modal-options.datatype';
import { ModalService } from './../../services/modal.service';
import { StateService } from './../../services/state.service';
import { DomainApitype } from './../../apitypes/Domain.apitype';
import { PagingApitype } from './../../apitypes/Paging.apitype';
import { DomainsOperation } from './../../operations/domains.operations';
import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { FormControl } from '@angular/forms';

import 'rxjs/add/operator/debounceTime';

@Component({
    selector: 'app-domains',
    templateUrl: './domains.component.html',
    styleUrls: ['./domains.component.scss']
})
export class DomainsComponent implements OnInit {

    public pagingInfo = new PagingApitype({});
    public pageRequested = 1;

    public domainList: DomainApitype[] = [];

    public sortField = '';
    public sortOrder = 'asc';

    public searchInput: FormControl;
    public typeFilter: FormControl;
    public typeFilterOptions = ['MASTER', 'NATIVE', 'SLAVE'];

    constructor(private domains: DomainsOperation, public gs: StateService, private modal: ModalService, private router: Router) { }

    public ngOnInit() {
        this.searchInput = new FormControl('');
        this.searchInput.valueChanges.debounceTime(500).subscribe(() => this.loadData());

        this.typeFilter = new FormControl(null);
        this.typeFilter.valueChanges.subscribe(() => this.loadData());

        this.loadData();
    }

    public async loadData() {
        const sortStr = this.sortField !== '' ? this.sortField + '-' + this.sortOrder : null;
        const searchStr = this.searchInput.value !== '' ? this.searchInput.value : null;
        const typeFilter = this.typeFilter.value;

        const res = await this.domains.getList(this.pageRequested, this.gs.pageSize, searchStr, sortStr, typeFilter);

        this.pagingInfo = res.paging;
        this.domainList = res.results;
    }

    public async onDeleteDomain(domain: DomainApitype) {
        try {
            await this.modal.showMessage(new ModalOptionsDatatype({
                heading: 'Confirm deletion',
                body: 'Are you shure you want to delete ' + domain.name + '?',
                acceptText: 'Delete',
                dismisText: 'Cancel',
                acceptClass: 'danger'
            }));

            await this.domains.delete(domain.id);

            await this.loadData();
        } catch (e) {
        }
    }

    public async onPageChange(newPage: number) {
        this.pageRequested = newPage;
        await this.loadData();
    }

    public async onPagesizeChange(pagesize: number) {
        this.gs.pageSize = pagesize;
        this.pageRequested = 1;
        await this.loadData();
    }

    public async onDomainClick(domain: DomainApitype) {
        if (domain.type === 'SLAVE') {
            this.router.navigate(['/domains/slave/', domain.id.toString()]);
        } else if (domain.type === 'MASTER') {
            this.router.navigate(['/domains/master/', domain.id.toString()]);
        } else if (domain.type === 'NATIVE') {
            this.router.navigate(['/domains/native/', domain.id.toString()]);
        }
    }

    public async onSortEvent(sortEvent: SortEventDatatype) {
        if (sortEvent.order === 0) {
            this.sortField = '';
            this.sortOrder = 'asc';
        } else {
            this.sortField = sortEvent.field;
            this.sortOrder = sortEvent.order === 1 ? 'asc' : 'desc';
        }

        await this.loadData();
    }
}
