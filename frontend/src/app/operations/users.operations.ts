import { PermissionApitype } from './../apitypes/Permission.apitype';
import { UserApitype } from './../apitypes/User.apitype';
import { ListApitype } from './../apitypes/List.apitype';
import { Injectable } from '@angular/core';
import { HttpService } from '../services/http.service';
import { StateService } from '../services/state.service';
import { SessionApitype } from '../apitypes/Session.apitype';

@Injectable()
export class UsersOperation {

    constructor(private http: HttpService, private gs: StateService) { }

    public async getList(page?: number, pageSize?: number, query?: string,
        sort?: Array<String> | string, type?: string): Promise<ListApitype<UserApitype>> {
        try {
            return new ListApitype<UserApitype>(await this.http.get('/users', {
                page: page,
                pagesize: pageSize,
                query: query,
                sort: sort,
                type: type
            }));
        } catch (e) {
            console.error(e);
            return new ListApitype<UserApitype>({ paging: {}, results: [] });
        }
    }

    public async delete(userId: number): Promise<boolean> {
        try {
            await this.http.delete(['/users', userId.toString()]);
            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }

    public async getSingle(userId: number): Promise<UserApitype> {
        try {
            return new UserApitype(await this.http.get(['/users', userId.toString()]));
        } catch (e) {
            console.error(e);
            return new UserApitype({});
        }
    }

    public async updateUser(userId: number, name?: string, type?: string, password?: string): Promise<boolean> {
        const data = {};
        if (name !== null && name !== undefined) {
            data['name'] = name;
        }
        if (type !== null && type !== undefined) {
            data['type'] = type;
        }
        if (password !== null && password !== undefined) {
            data['password'] = password;
        }

        try {
            await this.http.put(['/users', userId.toString()], data);

            return true;
        } catch (e) {
            if (e.response.status || e.response.status === 409) {
                throw new Error('User with that name already exists!');
            } else {
                console.error(e);
                return false;
            }
        }
    }

    public async create(name: string, type: string, password: string): Promise<UserApitype> {
        try {
            const result = new UserApitype(await this.http.post('/users', {
                name: name,
                type: type,
                password: password
            }));

            return result;
        } catch (e) {
            if (e.response.status || e.response.status === 409) {
                throw new Error('User already exists!');
            } else {
                console.error(e);
                return new UserApitype({});
            }
        }
    }

    public async getPermissions(page: number, pagesize: number, userId: number): Promise<ListApitype<PermissionApitype>> {
        try {
            return new ListApitype<PermissionApitype>(await this.http.get(['/users', userId.toString(), 'permissions'], {
                page: page,
                pagesize: pagesize
            }));
        } catch (e) {
            console.error(e);
            return new ListApitype<PermissionApitype>({ paging: {}, results: [] });
        }
    }

    public async removePermission(userId: number, domainId: number): Promise<void> {
        try {
            await this.http.delete(['/users', userId.toString(), 'permissions', domainId.toString()]);
        } catch (e) {
            console.error(e);
            return;
        }
    }

    public async addPermission(userId: number, domainId: number): Promise<void> {
        try {
            await this.http.post(['/users', userId.toString(), 'permissions'], { domainId: domainId });
        } catch (e) {
            console.error(e);
            return;
        }
    }
}
