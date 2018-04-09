import { DomainApitype } from './../apitypes/Domain.apitype';
import { ListApitype } from './../apitypes/List.apitype';
import { Injectable } from '@angular/core';
import { HttpService } from '../services/http.service';
import { StateService } from '../services/state.service';
import { SessionApitype } from '../apitypes/Session.apitype';

@Injectable()
export class DomainsOperation {

    constructor(private http: HttpService, private gs: StateService) { }

    public async getList(page?: number, pageSize?: number, query?: string, sort?: Array<String>): Promise<ListApitype<DomainApitype>> {
        try {
            return new ListApitype<DomainApitype>(await this.http.get('/domains', {
                page: page,
                pagesize: pageSize,
                query: query
            }));
        } catch (e) {
            console.error(e);
            return new ListApitype<DomainApitype>({ paging: {}, results: [] });
        }
    }

    public async delete(domainId: number): Promise<boolean> {
        try {
            await this.http.delete(['/domains', domainId.toString()]);
            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }
}
