import { AbstractControl } from '@angular/forms';

export class PasswordValidationUtil {

    static matchPassword(ac: AbstractControl) {
        const password = ac.get('password').value;
        const password2 = ac.get('password2').value;
        if (password !== password2) {
            ac.get('password2').setErrors({ matchPassword: true });
        } else {
            return null;
        }
    }
}
