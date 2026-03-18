import { Component, inject, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { InventarioService, Slot } from '../services/inventario.service';

export type TipusFilter = 'Tots' | 'Aigua' | 'Terra' | 'Aire';
export type MidaFilter = 'Tots' | 'Petit' | 'Mitjà'  | 'Gran';

const STARS_ARRAY = Array.from({ length: 40 }, () => ({
  x: parseFloat((Math.random() * 100).toFixed(1)),
  y: parseFloat((Math.random() * 100).toFixed(1)),
  delay: parseFloat((Math.random() * 2).toFixed(2)),
}));

@Component({
  selector: 'app-inventario',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './inventario.html',
  styleUrl: './inventario.css',
})
export class Inventario implements OnInit {

  // Inputs (recibir datos del padre):
  @Input() maxSlots!: number; // Número máximo de slots

  // Outputs (enviar datos al padre):
  @Output() slotSelected = new EventEmitter<Slot | null>(); // Slot seleccionado

  // inject() funciona en la zona de campos de clase 
  private inventarioService = inject(InventarioService);

  slots: Slot[] = [];

  tipusFilter: TipusFilter = 'Tots';
  midaFilter:  MidaFilter = 'Tots';


  ngOnInit(): void {
    this.inventarioService.cargarInventario();
    this.inventarioService.slots$.subscribe(slots => {
      this.slots = slots;
    });
  }

  // ── Getters filtrados ───────────────────────────
  get apilableSlots(): Slot[] {
    return this.slots
      .filter(s => s.apilable)
      .filter(s => this.passesFilter(s));
  }

  get noApilableSlots(): Slot[] {
    return this.slots
      .filter(s => !s.apilable)
      .filter(s => this.passesFilter(s));
  }

  private passesFilter(s: Slot): boolean {
    const okTipus = this.tipusFilter === 'Tots' || s.xuxe?.tipus === this.tipusFilter;
    const okMida = this.midaFilter === 'Tots' || s.xuxe?.mida  === this.midaFilter;
    return okTipus && okMida;
  }

  // ── Separar slots llenos / vacíos ───────────────────────────────
  get apilablesFills(): Slot[] { return this.apilableSlots.filter(s => !s.empty); }
  get apilablesEmpties(): Slot[] { return this.apilableSlots.filter(s =>  s.empty); }
  get noApilablesFills(): Slot[] { return this.noApilableSlots.filter(s => !s.empty); }
  get noApilablesEmpties():Slot[] { return this.noApilableSlots.filter(s =>  s.empty); }

  isFull(): boolean {
    return this.inventarioService.InventarioLleno();
  }

  // ── Interacciones ────────────────────────────────────────────────
  onSlotClick(slot: Slot): void {
    if (!slot.empty) {
      this.slotSelected.emit(slot);
    }
  }

  removeOne(slot: Slot, event: MouseEvent): void {
    event.stopPropagation();
    this.inventarioService.EliminarXuxesinv(slot.id);
  }

  // ── Helper para [ngClass] ────────────────────────────────────────
  slotClasses(slot: Slot): Record<string, boolean> {
    return {
      'slot': true,
      'slot--empty': slot.empty,
      'slot--filled': !slot.empty,
      'slot--tipus-aigua': slot.xuxe?.tipus === 'Aigua',
      'slot--tipus-terra': slot.xuxe?.tipus === 'Terra',
      'slot--tipus-aire': slot.xuxe?.tipus === 'Aire',
      'slot--mida-petit': slot.xuxe?.mida  === 'Petit',
      'slot--mida-mitja': slot.xuxe?.mida  === 'Mitjà',
      'slot--mida-gran': slot.xuxe?.mida  === 'Gran',
      'slot--stack-max': (slot.cantidad ?? 0) === 5,
    };
  }
}