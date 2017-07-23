import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { NavbarEntryComponent } from './navbar-entry.component';

describe('NavbarEntryComponent', () => {
  let component: NavbarEntryComponent;
  let fixture: ComponentFixture<NavbarEntryComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ NavbarEntryComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(NavbarEntryComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should be created', () => {
    expect(component).toBeTruthy();
  });
});
