import { StateService } from './state.service';
import { Injectable } from '@angular/core';
import { CanActivate, Router, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';

@Injectable()
export class AdminGuard implements CanActivate {

    constructor(private gs: StateService, private router: Router) { }

    canActivate(): boolean {
        if (!this.gs.isAdmin) {
            this.router.navigate(['/']);
            return false;
        } else {
            return true;
        }
    }
}
