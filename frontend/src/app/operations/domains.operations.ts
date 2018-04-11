import { SoaApitype } from './../apitypes/Soa.apitype';
import { SearchService, SearchServiceResult } from './../utils/search-service.interface';
import { DomainApitype } from './../apitypes/Domain.apitype';
import { ListApitype } from './../apitypes/List.apitype';
import { Injectable } from '@angular/core';
import { HttpService } from '../services/http.service';
import { StateService } from '../services/state.service';
import { SessionApitype } from '../apitypes/Session.apitype';

@Injectable()
export class DomainsOperation implements SearchService {

    constructor(private http: HttpService, private gs: StateService) { }

    public async search(query: string): Promise<SearchServiceResult[]> {
        const result = await this.getList(1, 10, query, null);

        return result.results.map((v: DomainApitype) => ({ id: v.id, text: v.name }));
    }

    public async getList(page?: number, pageSize?: number, query?: string,
        sort?: Array<String> | string, type?: string): Promise<ListApitype<DomainApitype>> {
        try {
            return new ListApitype<DomainApitype>(await this.http.get('/domains', {
                page: page,
                pagesize: pageSize,
                query: query,
                sort: sort,
                type: type
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

    public async getSingle(domainId: number): Promise<DomainApitype> {
        try {
            return new DomainApitype(await this.http.get(['/domains', domainId.toString()]));
        } catch (e) {
            console.error(e);
            return new DomainApitype({});
        }
    }

    public async updateMaster(domainId: number, master: string): Promise<boolean> {
        try {
            await this.http.put(['/domains', domainId.toString()], {
                master: master
            });

            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }

    public async create(name: string, type: string, master?: string): Promise<DomainApitype> {
        let result: DomainApitype;
        try {
            if (type === 'SLAVE') {
                result = new DomainApitype(await this.http.post('/domains', {
                    name: name,
                    type: type,
                    master: master
                }));
            } else {
                result = new DomainApitype(await this.http.post('/domains', {
                    name: name,
                    type: type
                }));
            }

            return result;
        } catch (e) {
            if (e.response.status || e.response.status === 409) {
                throw new Error('Domain already exists!');
            } else {
                console.error(e);
                return new DomainApitype({});
            }
        }
    }

    public async setSoa(domainId: number, primary: string, email: string, refresh: number,
        retry: number, expire: number, ttl: number): Promise<boolean> {
        try {
            await this.http.put(['/domains', domainId.toString(), 'soa'], {
                primary: primary,
                email: email,
                refresh: refresh,
                retry: retry,
                expire: expire,
                ttl: ttl
            });

            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }

    public async getSoa(domainId: number) {
        try {
            return new SoaApitype(await this.http.get(['/domains', domainId.toString(), 'soa']));
        } catch (e) {
            console.error(e);
            return false;
        }
    }
}
