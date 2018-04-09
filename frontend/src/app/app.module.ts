import { PagesizeComponent } from './partials/pagesize/pagesize.component';
import { PagingComponent } from './partials/paging/paging.component';
import { DomainsOperation } from './operations/domains.operations';
import { PasswordOperation } from './operations/password.operations';
import { AuthGuard } from './services/auth-guard.service';
import { FocusDirective } from './utils/Focus.directive';
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
import { PasswordComponent } from './pages/password/password.component';
import { EditSlaveComponent } from './pages/edit-slave/edit-slave.component';
import { EditAuthComponent } from './pages/edit-auth/edit-auth.component';
import { SelectComponent } from './partials/select/select.component';

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
    DomainsComponent,
    FocusDirective,
    PasswordComponent,
    PagingComponent,
    PagesizeComponent,
    EditSlaveComponent,
    EditAuthComponent,
    SelectComponent
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
    SessionOperation,
    PasswordOperation,
    DomainsOperation,
    AuthGuard
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
