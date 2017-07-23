import {NgModule} from '@angular/core';
import {Routes, RouterModule} from '@angular/router';

import {DomainsComponent} from './pages/domains/domains.component';
import {UsersComponent} from './pages/users/users.component';
import {LoginComponent} from './pages/login/login.component';

const routes: Routes = [
    {
        path: 'domains',
        component: DomainsComponent
    },
    {
        path: 'users',
        component: UsersComponent
    },
    {
        path: '',
        component: LoginComponent,
        pathMatch: 'full'
    },
    {
        path: '',
        redirectTo: '/',
        pathMatch: 'prefix'
    }
];

@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule]
})
export class AppRoutingModule {}
