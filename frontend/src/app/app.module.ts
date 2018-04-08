import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';

import { AppComponent } from './app.component';
import { AlertComponent } from './partials/alert/alert.component';
import { AlertMessageComponent } from './partials/alert-message/alert-message.component';
import { FaIconComponent } from './partials/fa-icon/fa-icon.component';
import { NavbarComponent } from './partials/navbar/navbar.component';
import { NavbarEntryComponent } from './partials/navbar-entry/navbar-entry.component';
import { SortComponent } from './partials/sort/sort.component';
import { ModalContainerComponent } from './partials/modal-container/modal-container.component';
import { AppRoutingModule } from './app-routing.module';
import { ModalService } from './services/modal.service';
import { LoginComponent } from './pages/login/login.component';
import { StateService } from './services/state.service';
import { HttpService } from './services/http.service';
import { SessionOperation } from './operations/session.operation';
import { DomainsComponent } from './pages/domains/domains.component';

@NgModule({
  declarations: [
    AppComponent,
    AlertComponent,
    AlertMessageComponent,
    FaIconComponent,
    NavbarComponent,
    NavbarEntryComponent,
    SortComponent,
    ModalContainerComponent,
    LoginComponent,
    DomainsComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    ReactiveFormsModule
  ],
  providers: [
    ModalService,
    StateService,
    HttpService,
    SessionOperation
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
