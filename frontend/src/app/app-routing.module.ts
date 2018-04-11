import { NativeGuard } from './services/native-guard.service';
import { LoggedOutGuard } from './services/logged-out-guard.service';
import { CreateUserComponent } from './pages/create-user/create-user.component';
import { EditUserComponent } from './pages/edit-user/edit-user.component';
import { AdminGuard } from './services/admin-guard.service';
import { UsersComponent } from './pages/users/users.component';
import { CreateAuthComponent } from './pages/create-auth/create-auth.component';
import { CreateSlaveComponent } from './pages/create-slave/create-slave.component';
import { EditSlaveComponent } from './pages/edit-slave/edit-slave.component';
import { PasswordComponent } from './pages/password/password.component';
import { AuthGuard } from './services/auth-guard.service';
import { DomainsComponent } from './pages/domains/domains.component';
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { LoginComponent } from './pages/login/login.component';
import { EditAuthComponent } from './pages/edit-auth/edit-auth.component';

const routes: Routes = [
    {
        path: '',
        component: LoginComponent,
        pathMatch: 'full',
        canActivate: [LoggedOutGuard]
    },
    {
        path: 'logout',
        component: LoginComponent,
        data: { logout: true },
        canActivate: [LoggedOutGuard]
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
                path: 'domains/slave/:domainId',
                component: EditSlaveComponent
            },
            {
                path: 'domains/master/:domainId',
                component: EditAuthComponent,
                data: { type: 'MASTER' }
            },
            {
                path: 'domains/native/:domainId',
                component: EditAuthComponent,
                data: { type: 'NATIVE' }
            },
            {
                path: '',
                canActivate: [AdminGuard],
                children: [
                    {
                        path: 'domains/create/slave',
                        component: CreateSlaveComponent
                    },
                    {
                        path: 'domains/create/master',
                        component: CreateAuthComponent,
                        data: { type: 'MASTER' }
                    },
                    {
                        path: 'domains/create/native',
                        component: CreateAuthComponent,
                        data: { type: 'NATIVE' }
                    },
                    {
                        path: 'users',
                        component: UsersComponent
                    },
                    {
                        path: 'users/create',
                        component: CreateUserComponent
                    },
                    {
                        path: 'users/:userId',
                        component: EditUserComponent
                    }
                ]
            },
            {
                path: 'password',
                component: PasswordComponent,
                canActivate: [NativeGuard]
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
