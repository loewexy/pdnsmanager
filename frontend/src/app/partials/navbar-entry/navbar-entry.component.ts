import { Component, Input } from '@angular/core';

@Component({
    selector: 'app-navbar-entry',
    templateUrl: './navbar-entry.component.html',
    styleUrls: ['./navbar-entry.component.scss']
})
export class NavbarEntryComponent {

    @Input() icon: string;
    @Input() target: string;
    @Input() neverActive = false;

    constructor() { }
}
