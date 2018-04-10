import { ActivatedRoute } from '@angular/router';
import { Component, OnInit } from '@angular/core';

@Component({
    selector: 'app-edit-auth',
    templateUrl: './edit-auth.component.html',
    styleUrls: ['./edit-auth.component.scss']
})
export class EditAuthComponent implements OnInit {

    public type: string;

    constructor(private route: ActivatedRoute) { }

    ngOnInit() {
        this.route.data.subscribe((data) => this.type = data.type);
    }

}
