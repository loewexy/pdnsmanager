import { Injectable } from '@angular/core';

import { HttpService } from 'app/services/http/http.service';
import { DomainsAnswer } from 'app/interfaces/domains-answer';

@Injectable()
export class DomainsService {

    constructor(private httpService: HttpService) { }

    /**
     * Gets a domain list from the API
     *
     * @param page          The page which should be loaded from the API
     * @param field         The field to order the results by
     * @param order         If the results should be ordered asc or desc
     * @param searchName    A string which is used as searchstring on the name
     * @param searchType    The type of domains which should be retrieved
     *
     * @returns A Promise for a DomainsAnswer object
     */
    getDomains(page: number, field: string, order: number, searchName: string, searchType: string) {
        const _order = order === 1 ? 1 : 0;

        return new Promise((resolve, reject) => {
            const body: any = {
                sort: {
                    field: field,
                    order: _order
                },
                action: 'getDomains',
                page: page
            };

            if (searchName) {
                body.name = searchName;
            }
            if (searchType) {
                body.type = searchType;
            }

            this.httpService.post<DomainsAnswer>('api/domains.php', body)
                .then((data: DomainsAnswer) => {
                    resolve(data);
                }, (err) => {
                    reject(err);
                });
        });
    }

    /**
     * Deletes one domain.
     *
     * @param id    Id of the domain to delete
     *
     * @returns A Promise for an empty content
     */
    deleteDomain(id: number) {
        return new Promise((resolve, reject) => {
            const body = {
                action: 'deleteDomain',
                id: id
            };

            this.httpService.post<{}>('api/domains.php', body)
                .then(() => {
                    resolve();
                }, (err) => {
                    reject(err);
                });
        });
    }

}
