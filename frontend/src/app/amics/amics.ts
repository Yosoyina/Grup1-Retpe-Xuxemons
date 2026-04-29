import { Component, OnDestroy, ChangeDetectorRef, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { debounceTime, distinctUntilChanged, filter, merge, switchMap, Subscription, Subject } from 'rxjs';
import { AmicsService, Amic, PeticioAmistat, PeticioAmistadEnviada } from '../services/amics.service';
import { ConfirmDialogComponent } from '../shared/confirm-dialog/confirm-dialog';

/**
 * Component de gestió d'amistats.
 *
 * Permet cercar usuaris, enviar i gestionar sol·licituds d'amistat,
 * acceptar o rebutjar peticions rebudes i eliminar amics amb animació.
 * Es refresca automàticament cada 30 segons i en tornar el focus a la finestra.
 */
@Component({
  selector: 'app-amics',
  imports: [CommonModule, ReactiveFormsModule, ConfirmDialogComponent],
  templateUrl: './amics.html',
  styleUrl: './amics.css',
})
export class Amics implements OnDestroy {
  cercaBusqueda = new FormControl('');
  resultatsCerca: Amic[] = [];
  cercant = false;

  amics: Amic[] = [];
  // Pot diferir d'amics durant l'animació de sortida d'un element eliminat
  amicsVisibles: Amic[] = [];
  peticionsRebudes: PeticioAmistat[] = [];
  peticionsEnviades: PeticioAmistadEnviada[] = [];

  missatgeExit = '';
  missatgeError = '';

  confirmantEliminar: number | null = null;
  amicNouId: number | null = null;
  amicEliminantId: number | null = null;

  // Desa l'ID que s'ha d'animar fins que arribi en el BehaviorSubject
  private amicPendentAnimacioId: number | null = null;
  private subs: Subscription[] = [];
  // Subject per disparar cerques manuals sense duplicar la petició del stream reactiu
  private cercaManual$ = new Subject<string>();
  private timeoutAnimacioEntrada: ReturnType<typeof setTimeout> | null = null;
  private timeoutAnimacioSortida: ReturnType<typeof setTimeout> | null = null;
  private refreshInterval: ReturnType<typeof setInterval> | null = null;

  constructor(
    private amicsService: AmicsService,
    private router: Router,
    private cdr: ChangeDetectorRef,
  ) {
    this.amicsService.carregarAmics();
    this.amicsService.carregarPeticionsRebudes();
    this.amicsService.carregarPeticionsEnviades();

    // Refresc periòdic cada 30 s per detectar si un altre usuari ens ha esborrat
    this.refreshInterval = setInterval(() => {
      this.amicsService.carregarAmics();
      this.amicsService.carregarPeticionsRebudes();
      this.amicsService.carregarPeticionsEnviades();
    }, 30_000);

    this.subs.push(
      this.amicsService.amics.subscribe(amics => {
        const idsAbans = new Set(this.amics.map(amic => amic.id));

        this.amics = amics;
        this.amicsVisibles = amics.filter(amic => amic.id !== this.amicEliminantId);

        // Si l'amic nou ja ha arribat al BehaviorSubject, llancem l'animació d'entrada
        if (
          this.amicPendentAnimacioId !== null &&
          !idsAbans.has(this.amicPendentAnimacioId) &&
          amics.some(amic => amic.id === this.amicPendentAnimacioId)
        ) {
          this.activarAnimacioNouAmic(this.amicPendentAnimacioId);
          this.amicPendentAnimacioId = null;
        }

        this.cdr.markForCheck();
      }),
      this.amicsService.peticionsRebudes.subscribe(peticions => {
        this.peticionsRebudes = peticions;
        this.cdr.markForCheck();
      }),
      this.amicsService.peticionsEnviades.subscribe(peticions => {
        this.peticionsEnviades = peticions;
        this.cdr.markForCheck();
      }),
    );

    this.subs.push(
      // Stream unificat: debounce del camp de text + emissions manuals del botó/Enter
      merge(
        this.cercaBusqueda.valueChanges.pipe(debounceTime(300), distinctUntilChanged()),
        this.cercaManual$
      ).pipe(
        filter(q => (q ?? '').trim().length >= 3),
        switchMap(q => {
          this.cercant = true;
          this.cdr.markForCheck();
          return this.amicsService.cercarUsuaris(q!.trim());
        }),
      ).subscribe({
        next: resultats => {
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

    this.subs.push(
      this.cercaBusqueda.valueChanges.pipe(
        filter(q => (q ?? '').trim().length < 3),
      ).subscribe(() => {
        this.resultatsCerca = [];
        this.cdr.markForCheck();
      })
    );
  }

  // Refresca quan l'usuari torna a la pestanya (l'amic pot haver esborrat mentre estava fora)
  @HostListener('window:focus')
  onWindowFocus(): void {
    this.amicsService.carregarAmics();
    this.amicsService.carregarPeticionsRebudes();
    this.amicsService.carregarPeticionsEnviades();
  }

  ngOnDestroy(): void {
    this.subs.forEach(sub => sub.unsubscribe());
    this.cercaManual$.complete();
    if (this.timeoutAnimacioEntrada) clearTimeout(this.timeoutAnimacioEntrada);
    if (this.timeoutAnimacioSortida) clearTimeout(this.timeoutAnimacioSortida);
    if (this.refreshInterval) clearInterval(this.refreshInterval);
  }

  esAmic(id: number): boolean {
    return this.amics.some(amic => amic.id === id);
  }

  peticioPendent(id: number): boolean {
    return this.peticionsRebudes.some(peticio => peticio.remitente.id === id);
  }

  peticioPendentEnviada(id: number): boolean {
    return this.peticionsEnviades.some(peticio => peticio.destinatario.id === id);
  }

  enviarPeticio(destinatari: Amic): void {
    this.amicsService.enviarPeticio(destinatari.id).subscribe({
      next: () => {
        this.mostrarExit(`Solicitud enviada a ${destinatari.nombre}!`);
      },
      error: err => {
        this.mostrarError(err.error?.errors?.destinatarioId?.[0] ?? err.error?.message ?? 'Error al enviar la solicitud.');
      },
    });
  }

  // Guarda l'ID per animar-lo quan arribi al BehaviorSubject després d'acceptar
  acceptarPeticio(peticio: PeticioAmistat): void {
    this.amicPendentAnimacioId = peticio.remitente.id;

    this.amicsService.acceptarPeticio(peticio.id).subscribe({
      next: () => this.mostrarExit(`${peticio.remitente.nombre} ahora es amigo tuyo!`),
      error: () => {
        this.amicPendentAnimacioId = null;
        this.mostrarError('Error al aceptar la solicitud.');
      },
    });
  }

  rebutjarPeticio(peticio: PeticioAmistat): void {
    this.amicsService.rebutjarPeticio(peticio.id).subscribe({
      next: () => this.mostrarExit('Solicitud rechazada.'),
      error: () => this.mostrarError('Error al rechazar la solicitud.'),
    });
  }

  get amicAConfirmar(): Amic | null {
    return this.amics.find(a => a.id === this.confirmantEliminar) ?? null;
  }

  onConfirmatEliminar(): void {
    const amic = this.amicAConfirmar;
    if (amic) this.eliminarAmic(amic);
    else this.confirmantEliminar = null;
  }

  confirmarEliminar(id: number): void {
    this.confirmantEliminar = id;
  }

  cancelarEliminar(): void {
    this.confirmantEliminar = null;
  }

  // Espera ~420 ms (durada de l'animació CSS de sortida) abans de cridar la API
  eliminarAmic(amic: Amic): void {
    if (this.amicEliminantId !== null) return;

    this.confirmantEliminar = null;
    this.amicEliminantId = amic.id;
    this.cdr.markForCheck();

    if (this.timeoutAnimacioSortida) clearTimeout(this.timeoutAnimacioSortida);
    this.timeoutAnimacioSortida = setTimeout(() => {
      this.amicsVisibles = this.amicsVisibles.filter(item => item.id !== amic.id);
      this.cdr.markForCheck();

      this.amicsService.eliminarAmic(amic.id).subscribe({
        next: () => {
          this.amicEliminantId = null;
          this.mostrarExit(`${amic.nombre} eliminado de la lista de amigos.`);
        },
        error: () => {
          this.amicEliminantId = null;
          this.amicsVisibles = [...this.amics];
          this.mostrarError('Error al eliminar el amigo.');
          this.cdr.markForCheck();
        },
      });
    }, 420);
  }

  getAvatarSrc(avatar: string | null): string {
    if (!avatar || avatar.startsWith('avatarpordefecto')) return '/avatarpordefecto.webp';
    return '/Imatges/Xuxemons/' + avatar;
  }

  // Emet al Subject compartit sense fer una crida HTTP addicional
  cercar(): void {
    const q = (this.cercaBusqueda.value ?? '').trim();
    if (q.length >= 3) {
      this.cercaManual$.next(q);
    }
  }

  sortir(): void {
    this.router.navigate(['/menu-principal']);
  }

  esAmicNou(id: number): boolean {
    return this.amicNouId === id;
  }

  estaEliminantAmic(id: number): boolean {
    return this.amicEliminantId === id;
  }

  private mostrarExit(msg: string): void {
    this.missatgeExit = msg;
    this.missatgeError = '';
    this.cdr.markForCheck();
    setTimeout(() => {
      this.missatgeExit = '';
      this.cdr.markForCheck();
    }, 3500);
  }

  private mostrarError(msg: string): void {
    this.missatgeError = msg;
    this.missatgeExit = '';
    this.cdr.markForCheck();
    setTimeout(() => {
      this.missatgeError = '';
      this.cdr.markForCheck();
    }, 3500);
  }

  // Marca l'amic com a nou durant 1,8 s perquè la plantilla apliqui l'animació d'entrada
  private activarAnimacioNouAmic(id: number): void {
    this.amicNouId = id;

    if (this.timeoutAnimacioEntrada) clearTimeout(this.timeoutAnimacioEntrada);
    this.timeoutAnimacioEntrada = setTimeout(() => {
      this.amicNouId = null;
      this.cdr.markForCheck();
    }, 1800);
  }
}