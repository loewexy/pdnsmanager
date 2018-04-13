import { Router } from '@angular/router';
import { Injectable } from '@angular/core';

import { AxiosInstance, AxiosResponse, AxiosError } from 'axios';
import axios from 'axios';
import { StateService } from './state.service';
import { ModalService } from './modal.service';
import { ModalOptionsDatatype } from '../datatypes/modal-options.datatype';

@Injectable()
export class HttpService {

    http: AxiosInstance;

    constructor(private gs: StateService, private router: Router, private modal: ModalService) {
        this.http = axios.create({
            baseURL: 'api/v1/'
        });
    }

    public async get(url: string | Array<string>, params: Object = {}): Promise<any> {
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

        const reqUrl = queryStr.length > 0 ? this.makeUrl(url) + '?' + queryStr : this.makeUrl(url);

        try {
            return (await this.http({
                url: reqUrl,
                method: 'get',
                headers: this.buildHeaders()
            })).data;
        } catch (e) {
            this.handleException(e);
        }
    }

    public async post(url: string | Array<string>, data: Object = {}): Promise<any> {
        try {
            return (await this.http({
                url: this.makeUrl(url),
                method: 'post',
                data: data,
                headers: this.buildHeaders()
            })).data;
        } catch (e) {
            this.handleException(e);
        }
    }

    public async put(url: string | Array<string>, data: Object = {}): Promise<any> {
        try {
            return (await this.http({
                url: this.makeUrl(url),
                method: 'put',
                data: data,
                headers: this.buildHeaders()
            })).data;
        } catch (e) {
            this.handleException(e);
        }
    }

    public async delete(url: string | Array<string>): Promise<any> {
        try {
            return (await this.http({
                url: this.makeUrl(url),
                method: 'delete',
                headers: this.buildHeaders()
            })).data;
        } catch (e) {
            this.handleException(e);
        }
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

    private async handleException(e: AxiosError) {
        if (e.response && e.response.status === 403 &&
            e.response.data.hasOwnProperty('code') &&
            e.response.data.code === 'invalid_session') {

            await this.modal.showMessage(new ModalOptionsDatatype({
                heading: 'Session expired!',
                body: 'Your session has been expired please log in again!',
                acceptText: 'OK',
                acceptClass: 'warning',
                dismisText: ''
            }));

            this.gs.apiToken = '';
            this.gs.isLoggedIn = false;

            this.router.navigate(['/']);
        } else {
            throw e;
        }
    }
}
