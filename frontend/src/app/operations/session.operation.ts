import { UserApitype } from './../apitypes/User.apitype';
import { Injectable } from '@angular/core';
import { HttpService } from '../services/http.service';
import { StateService } from '../services/state.service';
import { SessionApitype } from '../apitypes/Session.apitype';

@Injectable()
export class SessionOperation {

    constructor(private http: HttpService, private gs: StateService) { }

    public async login(username: string, password: string): Promise<boolean> {
        try {
            const session = new SessionApitype(await this.http.post('/sessions', {
                username: username,
                password: password
            }));

            this.gs.apiToken = session.token;
            this.gs.isLoggedIn = true;

            const user = new UserApitype(await this.http.get('/users/me'));

            this.gs.isAdmin = user.type === 'admin';
            this.gs.isNative = user.native;

            return true;
        } catch (e) {
            if (e.response.status !== 403) {
                console.error('Unknown login error', e);
            }

            return false;
        }
    }

    public async logout(): Promise<void> {
        try {
            await this.http.delete(['/sessions', this.gs.apiToken]);
        } catch (e) {
            console.error('Logout failed for unknown reason', e);
        } finally {
            this.gs.isLoggedIn = false;
            this.gs.apiToken = null;
        }
    }

}
