import { Router } from '@angular/router';
import { Component } from '@angular/core';
import { StateService } from './services/state.service';
import { SessionOperation } from './operations/session.operation';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent {

  constructor(public gs: StateService, private session: SessionOperation, private router: Router) { }

  public async onLogout() {
    await this.session.logout();
    this.router.navigate(['/logout']);
  }
}
