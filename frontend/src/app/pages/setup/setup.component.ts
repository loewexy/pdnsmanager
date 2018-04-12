import { HttpService } from './../../services/http.service';
import { PasswordValidationUtil } from './../../utils/password-validation.util';
import { Router } from '@angular/router';
import { FormGroup, Validators, FormBuilder } from '@angular/forms';
import { OnInit, Component } from '@angular/core';

@Component({
    selector: 'app-setup',
    templateUrl: './setup.component.html',
    styleUrls: ['./setup.component.scss']
})
export class SetupComponent implements OnInit {

    public setupForm: FormGroup;

    public errorMessage = '';

    public loading = false;

    constructor(private fb: FormBuilder, private router: Router, private http: HttpService) { }

    ngOnInit() {
        this.createForm();
    }

    private createForm() {
        this.setupForm = this.fb.group({
            db: this.fb.group({
                host: ['', Validators.required],
                user: ['', Validators.required],
                password: [''],
                database: ['', Validators.required],
                port: ['3306', [Validators.required, Validators.pattern(/^[0-9]+$/)]]
            }),
            admin: this.fb.group({
                name: ['', Validators.required],
                password: ['', Validators.required],
                password2: ['']
            }, { validator: PasswordValidationUtil.matchPassword })
        });
    }

    public async onSubmit() {
        this.errorMessage = '';
        this.setupForm.disable();
        this.loading = true;

        try {
            const res = await this.http.post('/setup', {
                db: this.setupForm.value.db,
                admin: {
                    name: this.setupForm.value.admin.name,
                    password: this.setupForm.value.admin.password
                }
            });

            this.router.navigate(['/']);
        } catch (e) {
            switch (e.response.status) {
                case 404:
                    this.errorMessage = 'The application has already been setup or the backend is misconfigured.';
                    break;
                case 500:
                    this.errorMessage = e.response.data.error;
                    break;
                default:
                    this.errorMessage = e.message;
                    break;
            }

            this.loading = false;
            this.setupForm.enable();
        }
    }
}
