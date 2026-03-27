import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject } from 'rxjs';

export type Tipus = 'Aigua' | 'Terra' | 'Aire';
export type Mida = 'Petit' | 'Mitjà' | 'Gran';

export interface Xuxes {
  id: number;
  nom?: string;
  nombre_xuxes?: string;
  emoji?: string;
  imagen?: string;
  tipus?: Tipus;
  mida?: Mida;
  apilable: boolean;
  descripcio?: string;
}

export interface Slot {
  id: number;
  inventario_id?: number; // ← afegim l'ID real de l'inventari
  apilable: boolean;
  empty: boolean;
  xuxe?: Xuxes;
  cantidad: number;
}

// Estructura de los datos que devuelve el backend para cada item del inventario
interface InventarioItemApi {
  id: number;
  xuxe: Xuxes;
  cantidad: number;
  apilable: boolean;
}

const API_URL = 'http://localhost:8000/api';
const MAX_STACK = 5;
const APILABLE_SLOTS = 10;
const NO_APILABLE_SLOTS = 10;

// Creamos 20 slots vacíos al inicio, 10 para apilables y 10 para no apilables
function CreaciondeSlots(): Slot[] {
  const slots: Slot[] = [];
  for (let i = 0; i < APILABLE_SLOTS; i++) {
    slots.push({ id: i, apilable: true, empty: true, cantidad: 0 });
  }
  for (let i = 0; i < NO_APILABLE_SLOTS; i++) {
    slots.push({ id: APILABLE_SLOTS + i, apilable: false, empty: true, cantidad: 0 });
  }
  return slots;
}

@Injectable({ providedIn: 'root' })
export class InventarioService {

  private http = inject(HttpClient);

  // Estado reactivo del inventario — emite cada vez que cambian las Xuxes guardadas
  private _slots$ = new BehaviorSubject<Slot[]>(CreaciondeSlots());
  readonly slots$ = this._slots$.asObservable();

  get slots(): Slot[] { return this._slots$.getValue(); }

  // ── Carga las Xuxes guardadas del jugador ───────
  cargarInventario(): void {
    this.http.get<{ items: InventarioItemApi[] }>(`${API_URL}/inventario`).subscribe({
      next: (response) => {
        const items = Array.isArray(response?.items) ? response.items : [];
        const slots = CreaciondeSlots();

        // Separa las Xuxes en apilables y no apilables con filter
        const apilables = items.filter(item => item.apilable);
        const noApilables = items.filter(item => !item.apilable);

        // Rellena los slots apilables con las Xuxes apilables del jugador
        apilables.forEach((item, index) => {
          if (index < APILABLE_SLOTS) {
            const normalizedXuxe = {
              ...item.xuxe,
              nom: item.xuxe?.nom ?? item.xuxe?.nombre_xuxes ?? '',
              tipus: item.xuxe?.tipus,
              mida: item.xuxe?.mida,
            } as Xuxes;

            slots[index] = {
              id: index,
              inventario_id: item.id,
              apilable: true,
              empty: false,
              xuxe: normalizedXuxe,
              cantidad: item.cantidad,
            };
          }
        });

        // Rellena los slots no apilables con las Xuxes no apilables del jugador
        noApilables.forEach((item, index) => {
          if (index < NO_APILABLE_SLOTS) {
            const normalizedXuxe = {
              ...item.xuxe,
              nom: item.xuxe?.nom ?? item.xuxe?.nombre_xuxes ?? '',
              tipus: item.xuxe?.tipus ?? 'N/A',
              mida: item.xuxe?.mida ?? 'N/A',
            } as Xuxes;

            slots[APILABLE_SLOTS + index] = {
              id: APILABLE_SLOTS + index,
              inventario_id: item.id,
              apilable: false,
              empty: false,
              xuxe: normalizedXuxe,
              cantidad: 1,
            };
          }
        });

        this._slots$.next(slots);
      },
      error: (err) => console.error('Error cargando el inventario:', err),
    });
  }

  // ── Comprueba si el inventario está lleno (20/20 slots ocupados) ─
  InventarioLleno(): boolean {
    return this.slots.every(s => !s.empty);
  }

  // ── Apartado para guardar una Xuxe en el inventario ─────────────────────────────
  GuardarXuxesinv(xux: Xuxes): boolean {
    const current = this.CopiarSlots();

    if (xux.apilable) {
      // Si ya existe una Xuxe del mismo tipo, apila una unidad más (máx. 5)
      const existing = current.find(
        s => s.apilable && !s.empty && s.xuxe?.id === xux.id && s.cantidad < MAX_STACK
      );
      if (existing) {
        existing.cantidad++;
        this._slots$.next(current);
        return true;
      }
      // Si no existe, busca un slot apilable vacío
      const empty = current.find(s => s.apilable && s.empty);
      if (!empty) return false;
      empty.empty = false; empty.xuxe = { ...xux }; empty.cantidad = 1;
    } else {
      // Las Xuxes no apilables ocupan siempre un slot individual
      const empty = current.find(s => !s.apilable && s.empty);
      if (!empty) return false;
      empty.empty = false; empty.xuxe = { ...xux }; empty.cantidad = 1;
    }

    this._slots$.next(current);
    return true;
  }

  // ── Eliminar una Xuxe del inventario del Jugador ────────────────────
  EliminarXuxesinv(slotId: number): void {
    const current = this.CopiarSlots();
    const slot = current.find(s => s.id === slotId);
    if (!slot || slot.empty) return;

    // Si es apilable y queda más de 1, solo resta una unidad
    if (slot.apilable && slot.cantidad > 1) {
      slot.cantidad--;
    } else {
      // Si es la última unidad, vacía el slot
      slot.empty = true; slot.xuxe = undefined; slot.cantidad = 0;
    }
    this._slots$.next(current);
  }

  // ── En este método se crea una copia de los slots para no modificar el original ────
  private CopiarSlots(): Slot[] {
    return this.slots.map(s => ({ ...s, xuxe: s.xuxe ? { ...s.xuxe } : undefined }));
  }
}