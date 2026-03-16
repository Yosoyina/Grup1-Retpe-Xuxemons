import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
 
export type Tipus = 'Aigua' | 'Terra' | 'Aire';
export type Mida  = 'Petit' | 'Mitjà'  | 'Gran';
 
export interface Xuxemon {
  id:  number;
  nom: string;
  emoji: string;
  tipus: Tipus;
  mida: Mida;
  apilable: boolean;
}
 
export interface Slot {
  id: number;
  apilable: boolean;
  empty: boolean;
  xuxemon?: Xuxemon;
  quantitat: number;
}
 
const MAX_STACK = 5;
const APILABLE_SLOTS = 10;
const NO_APILABLE_SLOTS = 10;
 
function buildEmptySlots(): Slot[] {
  const slots: Slot[] = [];
  for (let i = 0; i < APILABLE_SLOTS; i++) {
    slots.push({ id: i, apilable: true, empty: true, quantitat: 0 });
  }
  for (let i = 0; i < NO_APILABLE_SLOTS; i++) {
    slots.push({ id: APILABLE_SLOTS + i, apilable: false, empty: true, quantitat: 0 });
  }
  return slots;
}
 
@Injectable({ providedIn: 'root' })
export class InventarioService {
 
  private _slots$ = new BehaviorSubject<Slot[]>(buildEmptySlots());
  readonly slots$ = this._slots$.asObservable();
 
  get slots(): Slot[] { return this._slots$.getValue(); }
 
  isFull(): boolean {
    return this.slots.every(s => !s.empty);
  }
 
  addXuxemon(xux: Xuxemon): boolean {
    const current = this.cloneSlots();
 
    if (xux.apilable) {
      const existing = current.find(
        s => s.apilable && !s.empty && s.xuxemon?.id === xux.id
      );
      if (existing) {
        if (existing.quantitat < MAX_STACK) {
          existing.quantitat++;
          this._slots$.next(current);
          return true;
        }
        // Ya hay el máximo apilado para este tipo.
        return false;
      }
 
      const empty = current.find(s => s.apilable && s.empty);
      if (!empty) return false;
      empty.empty = false; empty.xuxemon = { ...xux }; empty.quantitat = 1;
    } else {
      const already = current.find(
        s => !s.apilable && !s.empty && s.xuxemon?.id === xux.id
      );
      if (already) return false;
 
      const empty = current.find(s => !s.apilable && s.empty);
      if (!empty) return false;
      empty.empty = false; empty.xuxemon = { ...xux }; empty.quantitat = 1;
    }
 
    this._slots$.next(current);
    return true;
  }
 
  removeFromSlot(slotId: number): void {
    const current = this.cloneSlots();
    const slot = current.find(s => s.id === slotId);
    if (!slot || slot.empty) return;
 
    if (slot.apilable && slot.quantitat > 1) {
      slot.quantitat--;
    } else {
      slot.empty = true; slot.xuxemon = undefined; slot.quantitat = 0;
    }
    this._slots$.next(current);
  }
 
  private cloneSlots(): Slot[] {
    return this.slots.map(s => ({ ...s, xuxemon: s.xuxemon ? { ...s.xuxemon } : undefined }));
  }
}