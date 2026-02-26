import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InfoUsuari } from './info-usuari';

describe('InfoUsuari', () => {
  let component: InfoUsuari;
  let fixture: ComponentFixture<InfoUsuari>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [InfoUsuari]
    })
    .compileComponents();

    fixture = TestBed.createComponent(InfoUsuari);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
