import { UserApitype } from './../apitypes/User.apitype';
import { Injectable } from '@angular/core';
import { HttpService } from '../services/http.service';
import { StateService } from '../services/state.service';
import { SessionApitype } from '../apitypes/Session.apitype';

@Injectable()
export class PasswordOperation {

    constructor(private http: HttpService, private gs: StateService) { }

    public async changePassword(password: string): Promise<boolean> {
        try {
            await this.http.put('/users/me', {
                password: password
            });

            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }
}
