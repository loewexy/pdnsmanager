import { Injectable } from '@angular/core';
import { Http, Headers, URLSearchParams } from '@angular/http';
import { HttpErrorResponse } from '@angular/common/http';

@Injectable()
export class HttpService {

    constructor(private http: Http) { }

    /*
     * Makes a GET request
     *
     * @param url       URL which should be used
     * @param params    A URLSearchParams object for the request params
     *
     * @returns A Promise for a object of type T
     */
    get<T>(url: string, params?: URLSearchParams) {
        return new Promise((resolve, reject) => {
            const headers = new Headers();
            headers.set('Content-Type', 'application/json')

            this.http.get(url, {
                headers: headers,
                params: params
            }).subscribe((res) => {
                resolve(<T> res.json() || {});
            }, (err: HttpErrorResponse) => {
                if (err.error instanceof Error) {
                    reject(err.error);
                } else {
                    reject(Error(`Backend returned code ${ err.status }`));
                }
            })
        });
    }

    /**
     * Makes a POST request
     *
     * @param url   URL which should be used
     * @param data  Data to use as body
     *
     * @returns A Promise for a object of type T
     */
    post<T>(url: string, data: any) {
        return new Promise((resolve, reject) => {
            const headers = new Headers();
            headers.set('Content-Type', 'application/json')

            this.http.post(url, data, {
                headers: headers
            }).subscribe((res) => {
                resolve(<T> res.json() || {});
            }, (err: HttpErrorResponse) => {
                if (err.error instanceof Error) {
                    reject(err.error);
                } else {
                    reject(Error(`Backend returned code ${ err.status }`));
                }
            })
        });
    }

}
