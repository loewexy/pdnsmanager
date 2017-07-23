import { Component } from '@angular/core';
import { Router } from '@angular/router';

import { SessionService } from './services/session/session.service';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss']
})
export class AppComponent {
    title = 'app';

    constructor(private sessionService: SessionService,
        private router: Router) { }

    /**
     * Starts the logout procedure using the SessionService.
     */
    logout() {
        this.sessionService.logOut()
            .then(() => {
                this.router.navigate(['/']);
            }, () => {

            });
    }
}
