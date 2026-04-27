import { Component, inject, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { InventarioService, Slot } from '../services/inventario.service';

@Component({
  selector: 'app-inventario',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './inventario.html',
  styleUrl: './inventario.css',
})
export class Inventario implements OnInit, OnDestroy {

  // Injecció de serveis i dependències
  private inventarioService = inject(InventarioService);
  private router = inject(Router);
  private cdr = inject(ChangeDetectorRef);
  private slotsSub!: Subscription;

  slots: Slot[] = [];
  slotSeleccionat: Slot | null = null;

  // Mètodes per gestionar l'inventari i la interacció amb els slots
  ngOnInit(): void {
    this.slotsSub = this.inventarioService.slots$.subscribe(slots => {
      this.slots = slots;
      this.cdr.markForCheck();
    });
    this.inventarioService.cargarInventario();
  }

  // Mètode per gestionar el clic en un slot, pot ser per eliminar o mostrar informació
  ngOnDestroy(): void {
    this.slotsSub?.unsubscribe();
  }

  // Getters per filtrar els slots segons les seves característiques
  get apilablesFills(): Slot[] { return this.slots.filter(s => s.apilable && !s.empty); }
  get apilablesEmpties(): Slot[] { return this.slots.filter(s => s.apilable && s.empty); }
  get noApilablesFills(): Slot[] { return this.slots.filter(s => !s.apilable && !s.empty); }
  get noApilablesEmpties(): Slot[] { return this.slots.filter(s => !s.apilable && s.empty); }

  // Mètode per comprovar si l'inventari està ple
  isFull(): boolean {
    return this.inventarioService.InventarioLleno();
  }

  // Mètode per obtenir les classes CSS d'un slot
  slotClasses(slot: Slot): Record<string, boolean> {
    return {
      'slot': true,
      'slot--empty': slot.empty,
      'slot--filled': !slot.empty,
    };
  }

  // Mètode per sortir de l'inventari i tornar al menú principal
  sortir(): void {
    this.router.navigate(['/menu-principal']);
  }

  seleccionar(slot: Slot): void {
    this.slotSeleccionat = this.slotSeleccionat?.id === slot.id ? null : slot;
  }

  tancarDetall(): void {
    this.slotSeleccionat = null;
  }
}