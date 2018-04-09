import { Injectable } from '@angular/core';

import { AxiosInstance, AxiosResponse } from 'axios';
import axios from 'axios';
import { StateService } from './state.service';

@Injectable()
export class HttpService {

    http: AxiosInstance;

    constructor(private gs: StateService) {
        this.http = axios.create({
            baseURL: 'api/v1/'
        });
    }

    public async get(url: string, params: Object = {}): Promise<any> {
        const parts = [];
        for (const [k, v] of Object.entries(params)) {
            if (v === undefined || v === null) {
                continue;
            }

            let value;
            if (v instanceof Array) {
                value = v.join(',');
            } else {
                value = v.toString();
            }

            parts.push(k + '=' + value);
        }

        const queryStr = parts.join('&');

        const reqUrl = queryStr.length > 0 ? this.makeUrl(url) + '?' + queryStr : url;

        return (await this.http({
            url: reqUrl,
            method: 'get',
            headers: this.buildHeaders()
        })).data;
    }

    public async post(url: string | Array<string>, data: Object = {}): Promise<any> {
        return (await this.http({
            url: this.makeUrl(url),
            method: 'post',
            data: data,
            headers: this.buildHeaders()
        })).data;
    }

    public async put(url: string | Array<string>, data: Object = {}): Promise<any> {
        return (await this.http({
            url: this.makeUrl(url),
            method: 'put',
            data: data,
            headers: this.buildHeaders()
        })).data;
    }

    public async delete(url: string | Array<string>): Promise<any> {
        return (await this.http({
            url: this.makeUrl(url),
            method: 'delete',
            headers: this.buildHeaders()
        })).data;
    }

    private buildHeaders(): Object {
        if (this.gs.apiToken !== null) {
            return {
                'X-Authentication': this.gs.apiToken
            };
        }
    }

    private makeUrl(url: string | Array<string>): string {
        if (url instanceof Array) {
            return url.join('/');
        } else {
            return url;
        }
    }
}
