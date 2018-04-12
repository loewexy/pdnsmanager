import { CredentialsOperation } from './operations/credentials.operations';
import { EditCredentialsComponent } from './pages/edit-credentials/edit-credentials.component';
import { EditAuthAddComponent } from './pages/edit-auth/edit-auth-add.component';
import { EditAuthLineComponent } from './pages/edit-auth/edit-auth-line.component';
import { RecordsOperation } from './operations/records.operations';
import { LoggedOutGuard } from './services/logged-out-guard.service';
import { NativeGuard } from './services/native-guard.service';
import { SearchComponent } from './partials/search/search.component';
import { CreateUserComponent } from './pages/create-user/create-user.component';
import { EditUserComponent } from './pages/edit-user/edit-user.component';
import { UsersOperation } from './operations/users.operations';
import { AdminGuard } from './services/admin-guard.service';
import { CreateAuthComponent } from './pages/create-auth/create-auth.component';
import { StopPropagateClickDirective } from './utils/stop-propagate-click.directive';
import { PagesizeComponent } from './partials/pagesize/pagesize.component';
import { PagingComponent } from './partials/paging/paging.component';
import { DomainsOperation } from './operations/domains.operations';
import { PasswordOperation } from './operations/password.operations';
import { AuthGuard } from './services/auth-guard.service';
import { FocusDirective } from './utils/focus.directive';
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
import { CreateSlaveComponent } from './pages/create-slave/create-slave.component';
import { UsersComponent } from './pages/users/users.component';
import { SetupComponent } from './pages/setup/setup.component';

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
    SelectComponent,
    StopPropagateClickDirective,
    CreateSlaveComponent,
    CreateAuthComponent,
    UsersComponent,
    EditUserComponent,
    CreateUserComponent,
    SearchComponent,
    EditAuthLineComponent,
    EditAuthAddComponent,
    EditCredentialsComponent,
    SetupComponent
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
    UsersOperation,
    RecordsOperation,
    CredentialsOperation,
    AuthGuard,
    AdminGuard,
    NativeGuard,
    LoggedOutGuard
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
