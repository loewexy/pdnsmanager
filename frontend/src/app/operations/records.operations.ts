import { RecordApitype } from './../apitypes/Record.apitype';
import { SoaApitype } from './../apitypes/Soa.apitype';
import { DomainApitype } from './../apitypes/Domain.apitype';
import { ListApitype } from './../apitypes/List.apitype';
import { Injectable } from '@angular/core';
import { HttpService } from '../services/http.service';
import { StateService } from '../services/state.service';
import { SessionApitype } from '../apitypes/Session.apitype';

@Injectable()
export class RecordsOperation {

    constructor(private http: HttpService, private gs: StateService) { }

    public async getListForDomain(domainId: number, page?: number, pageSize?: number, queryName?: string,
        type?: Array<string>, queryContent?: string, sort?: Array<String> | string, ): Promise<ListApitype<RecordApitype>> {
        try {
            return new ListApitype<RecordApitype>(await this.http.get('/records', {
                domain: domainId,
                page: page,
                pagesize: pageSize,
                queryName: queryName,
                type: type,
                queryContent: queryContent,
                sort: sort
            }));
        } catch (e) {
            console.error(e);
            return new ListApitype<RecordApitype>({ paging: {}, results: [] });
        }
    }

    public async delete(recordId: number): Promise<boolean> {
        try {
            await this.http.delete(['/records', recordId.toString()]);
            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }

    public async getSingle(recordId: number): Promise<RecordApitype> {
        try {
            return new RecordApitype(await this.http.get(['/records', recordId.toString()]));
        } catch (e) {
            console.error(e);
            return new RecordApitype({});
        }
    }

    public async updateRecord(recordId: number, name?: string, type?: string, content?: string,
        priority?: number, ttl?: number): Promise<boolean> {
        const data = {};
        if (name !== null && name !== undefined) {
            data['name'] = name;
        }
        if (type !== null && type !== undefined) {
            data['type'] = type;
        }
        if (content !== null && content !== undefined) {
            data['content'] = content;
        }
        if (priority !== null && priority !== undefined) {
            data['priority'] = priority;
        }
        if (ttl !== null && ttl !== undefined) {
            data['ttl'] = ttl;
        }

        try {
            await this.http.put(['/records', recordId.toString()], data);

            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }

    public async create(domainId: number, name: string, type: string, content: string,
        priority: number, ttl: number): Promise<RecordApitype> {
        try {
            const result = new RecordApitype(await this.http.post('/records', {
                name: name,
                type: type,
                content: content,
                priority: priority,
                ttl: ttl,
                domain: domainId
            }));

            return result;
        } catch (e) {
            console.error(e);
            return new RecordApitype({});
        }
    }
}
