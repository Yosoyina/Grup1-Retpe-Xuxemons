import { Component, inject, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { InventarioService, Slot } from '../services/inventario.service';

/**
 * Component de l'inventari del jugador.
 *
 * Mostra els slots d'items apilables i no apilables (xuxes i vacunes),
 * permet seleccionar un slot per veure'n els detalls
 * i navega de tornada al menú principal.
 */
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

  // ── CICLE DE VIDA ─────────────────────────────────────────────────────────

  // Subscriu l'estat dels slots i carrega l'inventari des del backend
  ngOnInit(): void {
    this.slotsSub = this.inventarioService.slots$.subscribe(slots => {
      this.slots = slots;
      this.cdr.markForCheck();
    });
    this.inventarioService.cargarInventario();
  }

  // Cancella la subscripció a l'observable en destruir el component
  ngOnDestroy(): void {
    this.slotsSub?.unsubscribe();
  }

  // ── GETTERS ─────────────────────────────────────────────────────────────────

  // Filtra els slots per tipus (apilable / no apilable) i estat (buit / ple)
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

  // ── NAVEGACIÓ ─────────────────────────────────────────────────────────────────

  // Navega de tornada al menú principal
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