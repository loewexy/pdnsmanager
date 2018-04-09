import { Directive, HostListener } from '@angular/core';

@Directive({
    selector: '[appStopPropagateClick]'
})
export class StopPropagateClickDirective {
    @HostListener('click', ['$event'])
    public onClick(event: any): void {
        event.stopPropagation();
    }
}
