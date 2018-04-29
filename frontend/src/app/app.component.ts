import { UpdateOperation } from './operations/update.operations';
import { Router } from '@angular/router';
import { Component, OnInit } from '@angular/core';
import { StateService } from './services/state.service';
import { SessionOperation } from './operations/session.operation';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss']
})
export class AppComponent implements OnInit {

    constructor(public gs: StateService, private session: SessionOperation, private router: Router, private update: UpdateOperation) { }

    async ngOnInit() {
        if (await this.update.updateRequired()) {
            this.router.navigate(['/update']);
        }
    }

    public async onLogout() {
        await this.session.logout();
        this.router.navigate(['/logout']);
    }
}
