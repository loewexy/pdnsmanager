import { FormControl } from '@angular/forms';
import { SearchService, SearchServiceResult } from './../../utils/search-service.interface';
import { Component, OnInit, Input, forwardRef, EventEmitter, Output } from '@angular/core';

@Component({
    selector: 'app-search',
    templateUrl: './search.component.html',
    styleUrls: ['./search.component.scss']
})
export class SearchComponent implements OnInit {

    @Input() searchService: SearchService;
    @Output() resultClicked = new EventEmitter<number>();

    public searchResults: SearchServiceResult[] = [];
    public searchComplete = false;

    public inputControl: FormControl;

    public disableDefokus = false;

    public open = false;

    constructor() { }

    ngOnInit() {
        this.inputControl = new FormControl('');
        this.inputControl.valueChanges.debounceTime(500).subscribe(() => this.reloadResults());
    }

    public async reloadResults() {
        if (this.inputControl.value.length === 0) {
            this.searchResults = [];
            this.searchComplete = false;
            return;
        }

        this.searchComplete = false;
        this.searchResults = await this.searchService.search(this.inputControl.value);
        this.searchComplete = true;
    }

    public toggleOpen() {
        this.open = !this.open;
    }

    public onClick(itemId: number) {
        this.inputControl.reset('');
        this.searchResults = [];
        this.searchComplete = false;
        this.open = false;
        this.resultClicked.emit(itemId);
    }

    public onEnter() {
        if (this.searchResults.length > 0) {
            const itemId = this.searchResults[0].id;
            this.inputControl.reset('');
            this.searchResults = [];
            this.searchComplete = false;
            this.resultClicked.emit(itemId);
        }
    }

    public onBlur() {
        setTimeout(() => { if (!this.disableDefokus) { this.open = false; } }, 100);
    }

    public hintText(): string | null {
        if (this.inputControl.value.length === 0) {
            return 'Type to search...';
        } else if (this.searchComplete && this.searchResults.length === 0) {
            return 'No results found.';
        } else if (!this.searchComplete) {
            return 'Searching...';
        } else {
            return null;
        }
    }

}
