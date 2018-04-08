import { Component, ViewChild, ElementRef, Input } from '@angular/core';

@Component({
    selector: 'app-navbar',
    templateUrl: './navbar.component.html',
    styleUrls: ['./navbar.component.scss']
})
export class NavbarComponent {
    @Input() brand: string;

    public open: Boolean = false;

    @ViewChild('navbarEntries') navbarEntries: any;
    @ViewChild('navbarEntriesRight') navbarEntriesRight: any;

    constructor() { }

    /**
     * Toggles if the menu is open.
     */
    toggleMenu() {
        this.open = !this.open;
    }

    /**
     * Returns true if both ng-content slots are empty.
     *
     * @returns true if the slots are empty, false otherwise
     */
    isEmpty() {
        let isEmpty = true;

        if (this.navbarEntries && this.navbarEntries.nativeElement.children) {
            if (this.navbarEntries.nativeElement.children.length > 0) {
                isEmpty = false;
            }
        }
        if (this.navbarEntriesRight && this.navbarEntriesRight.nativeElement.children) {
            if (this.navbarEntriesRight.nativeElement.children.length > 0) {
                isEmpty = false;
            }
        }

        return isEmpty;
    }
}
