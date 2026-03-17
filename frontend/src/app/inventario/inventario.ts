import { Component, inject, OnInit, input, output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { InventarioService, Slot } from '../services/inventario.service';

export type TipusFilter = 'Tots' | 'Aigua' | 'Terra' | 'Aire';
export type MidaFilter  = 'Tots' | 'Petit' | 'Mitjà'  | 'Gran';

const TIPUS_OPTIONS: TipusFilter[] = ['Tots', 'Aigua', 'Terra', 'Aire'];
const MIDA_OPTIONS:  MidaFilter[]  = ['Tots', 'Petit', 'Mitjà',  'Gran'];

const STARS_ARRAY = Array.from({ length: 40 }, () => ({
  x:     parseFloat((Math.random() * 100).toFixed(1)),
  y:     parseFloat((Math.random() * 100).toFixed(1)),
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
  maxSlots = input.required<number>(); // Número máximo de slots

  // Outputs (enviar datos al padre):
  slotSelected = output<Slot | null>(); // Slot seleccionado

  // inject() funciona en la zona de campos de clase 
  private inventarioService = inject(InventarioService);

  slots: Slot[] = [];

  tipusFilter: TipusFilter = 'Tots';
  midaFilter:  MidaFilter  = 'Tots';

  readonly tipusOptions = TIPUS_OPTIONS;
  readonly midaOptions  = MIDA_OPTIONS;
  readonly starsArray   = STARS_ARRAY;

  ngOnInit(): void {
    this.inventarioService.carregarInventari();
    this.inventarioService.slots$.subscribe(slots => {
      this.slots = slots;
    });
  }

  // ── Getters filtrados (usa .filter()) ───────────────────────────
  get apilableSlots(): Slot[] {
    return this.slots
      .filter(s =>  s.apilable)
      .filter(s => this.passesFilter(s));
  }

  get noApilableSlots(): Slot[] {
    return this.slots
      .filter(s => !s.apilable)
      .filter(s => this.passesFilter(s));
  }

  private passesFilter(s: Slot): boolean {
    const okTipus = this.tipusFilter === 'Tots' || s.xuxemon?.tipus === this.tipusFilter;
    const okMida  = this.midaFilter  === 'Tots' || s.xuxemon?.mida  === this.midaFilter;
    return okTipus && okMida;
  }

  // ── Separar slots llenos / vacíos ───────────────────────────────
  get apilablesFills():    Slot[] { return this.apilableSlots.filter(s => !s.empty); }
  get apilablesEmpties():  Slot[] { return this.apilableSlots.filter(s =>  s.empty); }
  get noApilablesFills():  Slot[] { return this.noApilableSlots.filter(s => !s.empty); }
  get noApilablesEmpties():Slot[] { return this.noApilableSlots.filter(s =>  s.empty); }

  isFull(): boolean {
    return this.inventarioService.isFull();
  }

  // ── Interacciones ────────────────────────────────────────────────
  onSlotClick(slot: Slot): void {
    if (!slot.empty) {
      this.slotSelected.emit(slot);
    }
  }

  removeOne(slot: Slot, event: MouseEvent): void {
    event.stopPropagation();
    this.inventarioService.removeFromSlot(slot.id);
  }

  // ── Helper para [ngClass] ────────────────────────────────────────
  slotClasses(slot: Slot): Record<string, boolean> {
    return {
      'slot':               true,
      'slot--empty':        slot.empty,
      'slot--filled':      !slot.empty,
      'slot--tipus-aigua':  slot.xuxemon?.tipus === 'Aigua',
      'slot--tipus-terra':  slot.xuxemon?.tipus === 'Terra',
      'slot--tipus-aire':   slot.xuxemon?.tipus === 'Aire',
      'slot--mida-petit':   slot.xuxemon?.mida  === 'Petit',
      'slot--mida-mitja':   slot.xuxemon?.mida  === 'Mitjà',
      'slot--mida-gran':    slot.xuxemon?.mida  === 'Gran',
      'slot--stack-max':   (slot.quantitat ?? 0) === 5,
    };
  }

  pillClasses(opt: string, current: string): Record<string, boolean> {
    return { 'pill': true, 'pill--active': opt === current };
  }
}