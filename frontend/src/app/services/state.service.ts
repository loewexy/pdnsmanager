import { Injectable } from '@angular/core';

@Injectable()
export class StateService {

    public _isLoggedIn = false;
    get isLoggedIn(): boolean {
        return this._isLoggedIn;
    }
    set isLoggedIn(_isLoggedIn: boolean) {
        this._isLoggedIn = _isLoggedIn;
        this.saveSessionStorage();
    }

    public _isAdmin = false;
    get isAdmin(): boolean {
        return this._isAdmin;
    }
    set isAdmin(_isAdmin: boolean) {
        this._isAdmin = _isAdmin;
        this.saveSessionStorage();
    }

    public _apiToken: string = null;
    get apiToken(): string {
        return this._apiToken;
    }
    set apiToken(_apiToken: string) {
        this._apiToken = _apiToken;
        this.saveSessionStorage();
    }

    public _isNative = false;
    get isNative(): boolean {
        return this._isNative;
    }
    set isNative(_isNative: boolean) {
        this._isNative = _isNative;
        this.saveSessionStorage();
    }

    constructor() {
        this.loadSessiontorage();
    }

    private saveSessionStorage() {
        sessionStorage.setItem('pdnsmanagerstate', JSON.stringify(this));
    }

    private loadSessiontorage() {
        Object.assign(this, JSON.parse(sessionStorage.getItem('pdnsmanagerstate')));
    }
}
