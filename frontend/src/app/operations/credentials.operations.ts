import { CredentialApitype } from './../apitypes/Credential.apitype';
import { SoaApitype } from './../apitypes/Soa.apitype';
import { DomainApitype } from './../apitypes/Domain.apitype';
import { ListApitype } from './../apitypes/List.apitype';
import { Injectable } from '@angular/core';
import { HttpService } from '../services/http.service';
import { StateService } from '../services/state.service';
import { SessionApitype } from '../apitypes/Session.apitype';

@Injectable()
export class CredentialsOperation {

    constructor(private http: HttpService, private gs: StateService) { }

    public async getList(recordId: number): Promise<ListApitype<CredentialApitype>> {
        try {
            return new ListApitype<CredentialApitype>(await this.http.get(['/records', recordId.toString(), 'credentials']));
        } catch (e) {
            console.error(e);
            return new ListApitype<CredentialApitype>({ paging: {}, results: [] });
        }
    }

    public async delete(recordId: number, credentialId: number): Promise<boolean> {
        try {
            await this.http.delete(['/records', recordId.toString(), 'credentials', credentialId.toString()]);
            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }

    public async getSingle(recordId: number, credentialId: number): Promise<CredentialApitype> {
        try {
            return new CredentialApitype(await this.http.get(['/records', recordId.toString(), 'credentials', credentialId.toString()]));
        } catch (e) {
            console.error(e);
            return new CredentialApitype({});
        }
    }

    public async updateKey(recordId: number, credentalId: number, description: string, key: string): Promise<boolean> {
        try {
            await this.http.put(['/records', recordId.toString(), 'credentials', credentalId.toString()], {
                description: description,
                key: key
            });

            return true;
        } catch (e) {
            if (e.response.status || e.response.status === 400) {
                throw new Error('The key is not a valid public key!');
            } else {
                console.error(e);
                return false;
            }
        }
    }

    public async updatePassword(recordId: number, credentalId: number, description: string, password: string): Promise<boolean> {
        try {
            const data = {
                description: description
            };

            if (password.length > 0) {
                data['password'] = password;
            }

            await this.http.put(['/records', recordId.toString(), 'credentials', credentalId.toString()], data);

            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }

    public async createKey(recordId: number, description: string, key: string): Promise<CredentialApitype> {
        try {
            const result = new DomainApitype(await this.http.post(['/records', recordId.toString(), 'credentials'], {
                description: description,
                type: 'key',
                key: key
            }));

        } catch (e) {
            if (e.response.status || e.response.status === 400) {
                throw new Error('The key is not a valid public key!');
            } else {
                console.error(e);
                return new CredentialApitype({});
            }
        }
    }

    public async createPassword(recordId: number, description: string, password: string): Promise<CredentialApitype> {
        try {
            const result = new DomainApitype(await this.http.post(['/records', recordId.toString(), 'credentials'], {
                description: description,
                type: 'password',
                password: password
            }));

        } catch (e) {
            console.error(e);
            return new CredentialApitype({});
        }
    }
}
