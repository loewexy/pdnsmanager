import {Component, OnInit, ViewChild} from '@angular/core';
import {Router, ActivatedRoute} from '@angular/router';

import {DomainsService} from 'app/services/domains/domains.service';
import {DomainsAnswer} from 'app/interfaces/domains-answer';
import {ModalService} from 'app/services/modal/modal.service';
import {SortComponent} from 'app/partials/sort/sort.component';
import {SortEvent} from 'app/interfaces/sort-event';

@Component({
    selector: 'app-domains',
    templateUrl: './domains.component.html',
    styleUrls: ['./domains.component.scss']
})
export class DomainsComponent implements OnInit {

    data: DomainsAnswer = {pages: {current: 1, total: 1}, data: []};

    @ViewChild('sortId') sortId: SortComponent;
    @ViewChild('sortName') sortName: SortComponent;
    @ViewChild('sortType') sortType: SortComponent;
    @ViewChild('sortRecords') sortRecords: SortComponent;

    private sortField: string = null;
    private sortOrder = 0;

    private searchName: string;
    private searchType = 'none';

    constructor(private domainsService: DomainsService,
        private modalService: ModalService,
        private router: Router,
        private route: ActivatedRoute) {}

    ngOnInit() {
        this.loadDomains();
    }

    /**
     * Loads the domains from the server using the parameters specified in
     * the properties of the components.
     */
    loadDomains() {
        const field = this.sortField ? this.sortField : '';
        const searchType = this.searchType === 'none' ? '' : this.searchType;

        this.domainsService.getDomains(1, field, this.sortOrder, this.searchName, searchType)
            .then((data: DomainsAnswer) => {
                this.data = data;
            });
    }

    /**
     * Delete a domain.
     *
     * @param id    Id of domain to delete
     * @param name  Name of domain to delete (only used for modal dialog)
     */
    deleteDomain(id: number, name: string) {
        this.modalService.showMessage({
            heading: 'Delete Domain?',
            body: `Are you shure you want to delete the zone ${name}?`,
            acceptText: 'Delete',
            dismisText: 'Cancel',
            acceptClass: 'danger'
        })
            .then(() => {
                this.domainsService.deleteDomain(id);
                this.loadDomains();
            }, () => {

            });
    }

    /**
     * Takes a sort event and applies the data to the current component state.
     *
     * @param event The SortEvent to process
     */
    sort(event: SortEvent) {
        this.sortId.resetIfNotField(event.field);
        this.sortName.resetIfNotField(event.field);
        this.sortType.resetIfNotField(event.field);
        this.sortRecords.resetIfNotField(event.field);

        if (event.order === 0) {
            this.sortField = null;
        } else {
            this.sortField = event.field;
        }

        this.sortOrder = event.order;

        this.loadDomains();
    }

    /**
     * Navigates to the domain edit page
     *
     * @param id    Id of the domain to edit
     */
    onClickDomain(id: Number) {
        this.router.navigate(['edit', id], {relativeTo: this.route});
    }

}
