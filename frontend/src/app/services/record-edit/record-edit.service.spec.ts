import { TestBed, inject } from '@angular/core/testing';

import { RecordEditService } from './record-edit.service';

describe('RecordEditService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [RecordEditService]
    });
  });

  it('should be created', inject([RecordEditService], (service: RecordEditService) => {
    expect(service).toBeTruthy();
  }));
});
