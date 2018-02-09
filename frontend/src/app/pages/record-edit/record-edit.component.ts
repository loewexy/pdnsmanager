import {Component, OnInit} from '@angular/core';
import {ActivatedRoute, ParamMap} from '@angular/router';

import {RecordEditService} from 'app/services/record-edit/record-edit.service';
import {DomainNameAnswer} from 'app/interfaces/domain-name-answer';

@Component({
    selector: 'app-record-edit',
    templateUrl: './record-edit.component.html',
    styleUrls: ['./record-edit.component.scss']
})
export class RecordEditComponent implements OnInit {

    private domainName = '';

    constructor(private route: ActivatedRoute,
        private recordEditService: RecordEditService) {}

    ngOnInit() {
        this.route.paramMap.subscribe((params: ParamMap) => {
            this.initDomain(parseInt(params.get('id'), 10));
        });
    }

    async initDomain(id: number) {
        this.domainName = (await this.recordEditService.getDomainName(id)).name;
    }

}
