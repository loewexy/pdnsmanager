import { Directive, AfterViewInit, ElementRef } from '@angular/core';

@Directive({
    selector: '[appFocus]'
})
export class FocusDirective implements AfterViewInit {

    constructor(private elementRef: ElementRef) { }

    ngAfterViewInit(): void {
        this.elementRef.nativeElement.focus();
    }

}
