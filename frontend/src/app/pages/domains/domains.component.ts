import { ModalOptionsDatatype } from './../../datatypes/modal-options.datatype';
import { ModalService } from './../../services/modal.service';
import { StateService } from './../../services/state.service';
import { DomainApitype } from './../../apitypes/Domain.apitype';
import { PagingApitype } from './../../apitypes/Paging.apitype';
import { DomainsOperation } from './../../operations/domains.operations';
import { Component, OnInit } from '@angular/core';

@Component({
    selector: 'app-domains',
    templateUrl: './domains.component.html',
    styleUrls: ['./domains.component.scss']
})
export class DomainsComponent implements OnInit {

    public pagingInfo = new PagingApitype({});
    public pageRequested = 1;

    public domainList: DomainApitype[] = [];

    constructor(private domains: DomainsOperation, public gs: StateService, private modal: ModalService) { }

    public ngOnInit() {
        this.loadData();
    }

    public async loadData() {
        const res = await this.domains.getList(this.pageRequested, this.gs.pageSize);

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
        alert(domain.id);
    }
}
