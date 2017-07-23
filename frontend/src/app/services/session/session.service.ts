import { Injectable } from '@angular/core';
import { HttpService } from 'app/services/http/http.service';

import { SessionAnswer } from 'app/interfaces/session-answer';

@Injectable()
export class SessionService {

    private isLoggedIn = false;
    private userType: string;

    constructor(private httpService: HttpService) {
        this.loadState();
    }

    /**
     * Returns if the user is logged in
     *
     * @returns true if the user is logged in, false otherwise
     */
    getIsLoggedIn() {
        return this.isLoggedIn;
    }

    /**
     * Returns the user type
     *
     * @returns User type of the current session
     */
    getUserType() {
        return this.userType;
    }

    /**
     * Tries a login to the server.
     *
     * @param username  Username to use
     * @param password  Password to use
     *
     * @returns A Promise
     */
    tryLogin(username: string, password: string) {
        return new Promise((resolve, reject) => {
            const body = { user: username, password: password };

            this.httpService.post<SessionAnswer>('api/index.php', body)
                .then((data: SessionAnswer) => {
                    if (data.status === 'success') {
                        this.isLoggedIn = true;
                        this.userType = 'admin';
                        this.saveState();
                        resolve();
                    } else {
                        reject(Error('Username or Password wrong'));
                    }
                }, (err) => {
                    reject(err);
                });
        });
    }

    /**
     * Sends a log out request to the server.
     *
     * @returns A Promise
     */
    logOut() {
        return new Promise((resolve, reject) => {
            this.httpService.get<any>('api/logout.php')
                .then(() => {
                    this.isLoggedIn = false;
                    this.userType = '';
                    this.saveState();
                    resolve();
                }, (err) => {
                    reject(err);
                });
        });
    }

    /**
     * Saves the state to the sessionStorage.
     */
    saveState() {
        sessionStorage.setItem('state', JSON.stringify({
            isLoggedIn: this.isLoggedIn,
            userType: this.userType
        }));
    }

    /**
     * Loads the state from the sessionStorage.
     */
    loadState() {
        const state = JSON.parse(sessionStorage.getItem('state'));
        if (state) {
            this.isLoggedIn = state.isLoggedIn;
            this.userType = state.userType;
        }
    }

}
