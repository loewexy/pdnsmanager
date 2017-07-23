import { Component, ViewChild } from '@angular/core';
import { Router } from '@angular/router';
import { NgForm } from '@angular/forms';

import { SessionService } from 'app/services/session/session.service';

@Component({
    selector: 'app-login',
    templateUrl: './login.component.html',
    styleUrls: ['./login.component.scss']
})
export class LoginComponent {
    @ViewChild('loginForm') loginForm: NgForm;

    private submited = false;
    private loginError = false;

    constructor(private sessionService: SessionService,
        private router: Router) { }

    /**
     * Tries a login atempt to the server using the data from the login form.
     * If this is successfull the login in performed, if not an error will be
     * shown.
     */
    tryLogin() {
        this.submited = true;
        if (!this.loginForm.valid) { return; }
        const value = this.loginForm.value;
        this.sessionService.tryLogin(value.username, value.password).then(() => {
            this.loginForm.reset();
            this.submited = false;
            this.loginError = false;
            this.router.navigate(['/domains']);
        }, () => {
            this.loginError = true;
        });
    }
}
