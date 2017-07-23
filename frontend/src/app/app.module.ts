import { BrowserModule } from '@angular/platform-browser';
import { FormsModule } from '@angular/forms';
import { NgModule } from '@angular/core';
import { HttpModule } from '@angular/http';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';

import { NavbarComponent } from './partials/navbar/navbar.component';
import { NavbarEntryComponent } from './partials/navbar-entry/navbar-entry.component';
import { FaIconComponent } from './partials/fa-icon/fa-icon.component';
import { AlertComponent } from './partials/alert/alert.component';
import { AlertMessageComponent } from './partials/alert-message/alert-message.component';
import { ModalContainerComponent } from './partials/modal-container/modal-container.component';
import { SortComponent } from './partials/sort/sort.component';

import { DomainsComponent } from './pages/domains/domains.component';
import { UsersComponent } from './pages/users/users.component';
import { LoginComponent } from './pages/login/login.component';

import { HttpService } from './services/http/http.service';
import { ModalService } from './services/modal/modal.service';
import { SessionService } from './services/session/session.service';
import { DomainsService } from './services/domains/domains.service';

@NgModule({
    declarations: [
        AppComponent,
        NavbarComponent,
        NavbarEntryComponent,
        FaIconComponent,
        DomainsComponent,
        UsersComponent,
        LoginComponent,
        AlertComponent,
        AlertMessageComponent,
        ModalContainerComponent,
        SortComponent
    ],
    imports: [
        BrowserModule,
        AppRoutingModule,
        FormsModule,
        HttpModule
    ],
    providers: [SessionService, HttpService, DomainsService, ModalService],
    bootstrap: [AppComponent]
})
export class AppModule { }
