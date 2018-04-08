import { DomainsComponent } from './pages/domains/domains.component';
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { LoginComponent } from './pages/login/login.component';

const routes: Routes = [
    {
        path: '',
        pathMatch: 'full',
        component: LoginComponent
    },
    {
        path: 'domains',
        pathMatch: 'full',
        component: DomainsComponent
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
export class AppRoutingModule { }
