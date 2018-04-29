import { UpdateOperation } from './../../operations/update.operations';
import { HttpService } from './../../services/http.service';
import { PasswordValidationUtil } from './../../utils/password-validation.util';
import { Router } from '@angular/router';
import { FormGroup, Validators, FormBuilder } from '@angular/forms';
import { OnInit, Component } from '@angular/core';
import { isString } from 'util';

@Component({
    selector: 'app-update',
    templateUrl: './update.component.html',
    styleUrls: ['./update.component.scss']
})
export class UpdateComponent implements OnInit {
    public errorMessage = '';

    public loading = false;

    public currentVersion = 0;
    public targetVersion = 0;

    constructor(private update: UpdateOperation, private router: Router) { }

    async ngOnInit() {
        const info = await this.update.updateStatus();

        if (!info.updateRequired) {
            this.router.navigate(['/']);
        }

        this.currentVersion = info.currentVersion;
        this.targetVersion = info.targetVersion;
    }

    public async onSubmit() {
        this.errorMessage = '';
        this.loading = true;

        const res = await this.update.doUpgrade();

        if (res === true) {
            this.router.navigate(['/']);
        } else {
            this.errorMessage = res.toString();
            this.loading = false;
        }
    }
}
