import { Component, Input } from '@angular/core';

@Component({
    selector: 'app-fa-icon',
    templateUrl: './fa-icon.component.html',
    styleUrls: ['./fa-icon.component.scss']
})
export class FaIconComponent {

    @Input() icon: string;
    @Input() size = 0;
    @Input() fixedWidth = false;
    @Input() animate = '';

    constructor() { }
}
