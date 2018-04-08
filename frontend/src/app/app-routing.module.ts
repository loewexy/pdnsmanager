import { AuthGuard } from './services/auth-guard.service';
import { DomainsComponent } from './pages/domains/domains.component';
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { LoginComponent } from './pages/login/login.component';

const routes: Routes = [
    {
        path: '',
        component: LoginComponent,
        pathMatch: 'full'
    },
    {
        path: 'logout',
        component: LoginComponent,
        data: { logout: true }
    },
    {
        path: '',
        pathMatch: 'prefix',
        canActivate: [AuthGuard],
        children: [
            {
                path: 'domains',
                component: DomainsComponent
            },
            {
                path: '**',
                redirectTo: '/'
            }
        ]
    }
];

@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule]
})
export class AppRoutingModule { }
