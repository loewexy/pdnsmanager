import { SessionApitype } from './../apitypes/Session.apitype';
import { Injectable } from '@angular/core';

@Injectable()
export class StateService {

    private _isLoggedIn = false;
    get isLoggedIn(): boolean {
        return this._isLoggedIn;
    }
    set isLoggedIn(_isLoggedIn: boolean) {
        this._isLoggedIn = _isLoggedIn;
        this.saveLocalStorage();
    }

    private _isAdmin = false;
    get isAdmin(): boolean {
        return this._isAdmin;
    }
    set isAdmin(_isAdmin: boolean) {
        this._isAdmin = _isAdmin;
        this.saveLocalStorage();
    }

    private _apiToken: string = null;
    get apiToken(): string {
        return this._apiToken;
    }
    set apiToken(_apiToken: string) {
        this._apiToken = _apiToken;
        this.saveLocalStorage();
    }

    private _isNative = false;
    get isNative(): boolean {
        return this._isNative;
    }
    set isNative(_isNative: boolean) {
        this._isNative = _isNative;
        this.saveLocalStorage();
    }

    private _pageSize = 25;
    get pageSize(): number {
        return this._pageSize;
    }
    set pageSize(_pageSize: number) {
        this._pageSize = _pageSize;
        this.saveLocalStorage();
    }

    private _pageSizes = [5, 10, 25, 50, 100];
    get pageSizes(): Array<number> {
        return this._pageSizes;
    }

    private _recordTypes = [
        'A', 'A6', 'AAAA', 'AFSDB', 'ALIAS', 'CAA', 'CDNSKEY', 'CDS', 'CERT', 'CNAME', 'DHCID',
        'DLV', 'DNAME', 'DNSKEY', 'DS', 'EUI48', 'EUI64', 'HINFO',
        'IPSECKEY', 'KEY', 'KX', 'LOC', 'LUA', 'MAILA', 'MAILB', 'MINFO', 'MR',
        'MX', 'NAPTR', 'NS', 'NSEC', 'NSEC3', 'NSEC3PARAM', 'OPENPGPKEY',
        'OPT', 'PTR', 'RKEY', 'RP', 'RRSIG', 'SIG', 'SPF',
        'SRV', 'TKEY', 'SSHFP', 'TLSA', 'TSIG', 'TXT', 'WKS', 'MBOXFW', 'URL'
    ];
    get recordTypes(): Array<string> {
        return this._recordTypes;
    }

    constructor() {
        this.loadLocalStorage();
    }

    private saveLocalStorage() {
        localStorage.setItem('pdnsmanagerstate', JSON.stringify(this));
    }

    private loadLocalStorage() {
        Object.assign(this, JSON.parse(localStorage.getItem('pdnsmanagerstate')));
    }
}
