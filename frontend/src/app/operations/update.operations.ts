import { UpdateApitype } from './../apitypes/Update.apitype';
import { PermissionApitype } from './../apitypes/Permission.apitype';
import { UserApitype } from './../apitypes/User.apitype';
import { ListApitype } from './../apitypes/List.apitype';
import { Injectable } from '@angular/core';
import { HttpService } from '../services/http.service';
import { StateService } from '../services/state.service';
import { SessionApitype } from '../apitypes/Session.apitype';

@Injectable()
export class UpdateOperation {

    constructor(private http: HttpService, private gs: StateService) { }

    public async updateRequired(): Promise<boolean> {
        return (await this.updateStatus()).updateRequired;
    }

    public async updateStatus(): Promise<UpdateApitype> {
        return new UpdateApitype(await this.http.get('/update'));
    }

    public async doUpgrade(): Promise<boolean | string> {
        try {
            await this.http.post('/update', { dummy: true });
            return true;
        } catch (e) {
            if (e.response.status === 500) {
                return e.response.data.error;
            } else {
                return e.message;
            }
        }
    }
}
