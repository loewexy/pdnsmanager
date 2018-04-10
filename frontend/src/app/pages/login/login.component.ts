import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { StateService } from '../../services/state.service';
import { HttpService } from '../../services/http.service';
import { SessionOperation } from '../../operations/session.operation';

@Component({
    selector: 'app-login',
    templateUrl: './login.component.html',
    styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {

    public loginForm: FormGroup;

    public loginError = false;

    public isLogoutPage = false;

    constructor(private router: Router, private fb: FormBuilder, public gs: StateService,
        private sessions: SessionOperation, private route: ActivatedRoute) {
        this.createForm();
    }

    ngOnInit(): void {
        this.route.data.subscribe((data) => this.isLogoutPage = data.logout);
    }

    private createForm() {
        this.loginForm = this.fb.group({
            username: ['', Validators.required],
            password: ['', Validators.required]
        });
    }

    public async onSubmit() {
        const v = this.loginForm.value;
        if (await this.sessions.login(v.username, v.password)) {
            this.loginError = false;
            this.loginForm.reset();
            this.router.navigate(['/domains']);
        } else {
            this.loginError = true;
        }
    }
}
