import {Injectable} from '@angular/core';

import {HttpService} from 'app/services/http/http.service';
import {DomainNameAnswer} from 'app/interfaces/domain-name-answer';

@Injectable()
export class RecordEditService {

    constructor(private httpService: HttpService) {}

    /**
       * Gets the name of the domain
       *
       * @param id    Id of the domain for which the name should be retrieved
       *
       * @returns A Promise for a DomainNameAnswer
       */
    getDomainName(id: number): Promise<DomainNameAnswer> {
        return new Promise((resolve, reject) => {
            const body = {
                action: 'getDomainName',
                domain: id
            };

            this.httpService.post<DomainNameAnswer>('api/edit-master.php', body)
                .then((answer: DomainNameAnswer) => {
                    resolve(answer);
                }, (err) => {
                    reject(err);
                });
        });
    }

}
