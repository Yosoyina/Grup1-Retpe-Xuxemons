import { Component, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { debounceTime, distinctUntilChanged, filter, switchMap, Subscription } from 'rxjs';
import { AmicsService, Amic, PeticioAmistat } from '../services/amics.service';

@Component({
  selector: 'app-amics',
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './amics.html',
  styleUrl: './amics.css',
})
export class Amics implements OnDestroy {

  cercaBusqueda = new FormControl('');
  resultatsCerca: Amic[] = [];
  cercant = false;

  amics: Amic[] = [];
  peticionsRebudes: PeticioAmistat[] = [];

  missatgeExit = '';
  missatgeError = '';

  confirmantEliminar: number | null = null;

  private subs: Subscription[] = [];

  constructor(
    private amicsService: AmicsService,
    private router: Router,
    private cdr: ChangeDetectorRef,
  ) {
    // carrega inicial
    this.amicsService.carregarAmics();
    this.amicsService.carregarPeticionsRebudes();

    // subscripcions als BehaviorSubjects
    this.subs.push(
      this.amicsService.amics.subscribe(a => {
        this.amics = a;
        this.cdr.markForCheck();
      }),
      this.amicsService.peticionsRebudes.subscribe(p => {
        this.peticionsRebudes = p;
        this.cdr.markForCheck();
      }),
    );

    // cerca amb debounce de 300ms i mínim 3 caràcters
    this.subs.push(
      this.cercaBusqueda.valueChanges.pipe(
        debounceTime(300),
        distinctUntilChanged(),
        filter(q => (q ?? '').trim().length >= 3),
        switchMap(q => {
          this.cercant = true;
          this.cdr.markForCheck();
          return this.amicsService.cercarUsuaris(q!.trim());
        }),
      ).subscribe({
        next: (resultats) => {
          this.resultatsCerca = resultats;
          this.cercant = false;
          this.cdr.markForCheck();
        },
        error: () => {
          this.cercant = false;
          this.cdr.markForCheck();
        },
      })
    );

    // quan el camp es buida o té menys de 3 caràcters, neteja resultats
    this.subs.push(
      this.cercaBusqueda.valueChanges.pipe(
        filter(q => (q ?? '').trim().length < 3),
      ).subscribe(() => {
        this.resultatsCerca = [];
        this.cdr.markForCheck();
      })
    );
  }

  ngOnDestroy(): void {
    this.subs.forEach(s => s.unsubscribe());
  }

  // retorna si ja som amics amb un usuari
  esAmic(id: number): boolean {
    return this.amics.some(a => a.id === id);
  }

  // retorna si ja hi ha una petició pendent amb un usuari
  peticioPendent(id: number): boolean {
    return this.peticionsRebudes.some(p => p.remitente.id === id);
  }

  enviarPeticio(destinatari: Amic): void {
    this.amicsService.enviarPeticio(destinatari.id).subscribe({
      next: () => {
        this.mostrarExit(`Sol·licitud enviada a ${destinatari.nombre}!`);
        this.amicsService.carregarPeticionsRebudes();
      },
      error: (err) => {
        this.mostrarError(err.error?.errors?.destinatarioId?.[0] ?? err.error?.message ?? 'Error en enviar la sol·licitud.');
      },
    });
  }

  acceptarPeticio(peticio: PeticioAmistat): void {
    this.amicsService.acceptarPeticio(peticio.id).subscribe({
      next: () => this.mostrarExit(`${peticio.remitente.nombre} ara és amic teu!`),
      error: () => this.mostrarError('Error en acceptar la sol·licitud.'),
    });
  }

  rebutjarPeticio(peticio: PeticioAmistat): void {
    this.amicsService.rebutjarPeticio(peticio.id).subscribe({
      next: () => this.mostrarExit('Sol·licitud rebutjada.'),
      error: () => this.mostrarError('Error en rebutjar la sol·licitud.'),
    });
  }

  confirmarEliminar(id: number): void {
    this.confirmantEliminar = id;
  }

  cancelarEliminar(): void {
    this.confirmantEliminar = null;
  }

  eliminarAmic(amic: Amic): void {
    this.amicsService.eliminarAmic(amic.id).subscribe({
      next: () => {
        this.confirmantEliminar = null;
        this.mostrarExit(`${amic.nombre} eliminat de la llista d'amics.`);
      },
      error: () => this.mostrarError('Error en eliminar l\'amic.'),
    });
  }

  getAvatarSrc(avatar: string | null): string {
    if (!avatar || avatar === 'avatarpordefecto.png') return '/avatarpordefecto.png';
    return '/Imatges/Xuxemons/' + avatar;
  }

  cercar(): void {
    const q = (this.cercaBusqueda.value ?? '').trim();
    if (q.length < 3) return;
    this.cercant = true;
    this.cdr.markForCheck();
    this.amicsService.cercarUsuaris(q).subscribe({
      next: (resultats) => {
        this.resultatsCerca = resultats;
        this.cercant = false;
        this.cdr.markForCheck();
      },
      error: () => {
        this.cercant = false;
        this.cdr.markForCheck();
      },
    });
  }

  sortir(): void {
    this.router.navigate(['/menu-principal']);
  }

  private mostrarExit(msg: string): void {
    this.missatgeExit = msg;
    this.missatgeError = '';
    this.cdr.markForCheck();
    setTimeout(() => { this.missatgeExit = ''; this.cdr.markForCheck(); }, 3500);
  }

  private mostrarError(msg: string): void {
    this.missatgeError = msg;
    this.missatgeExit = '';
    this.cdr.markForCheck();
    setTimeout(() => { this.missatgeError = ''; this.cdr.markForCheck(); }, 3500);
  }
}
