import { Component, OnDestroy, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule, AsyncPipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { BehaviorSubject, Subscription } from 'rxjs';
import { XuxemonService, Xuxemon, EtapaEvoluciones, FeedResult, AplicarVacunaResult } from '../services/xuxemon.service';
import { InventarioService, Slot } from '../services/inventario.service';

@Component({
  selector: 'app-xuxedex',
  standalone: true,
  imports: [CommonModule, AsyncPipe, FormsModule],
  templateUrl: './xuxedex.html',
  styleUrl: './xuxedex.css',
})
export class Xuxedex implements OnDestroy {
  paginaActual = 0;
  itemsPorPagina = 6;
  filtroActual = 'Todos';
  filtroMida = 'Tots';
  xuxemonSeleccionado: Xuxemon | null = null;
  xuxemons$: BehaviorSubject<Xuxemon[]>;
  cadenaEvolucio: EtapaEvoluciones[] = [];
  mostrarEvolucion = false;
  cargarEvolucion = false;
  errorEvolucion: string | null = null;
  xuxeEvoSlot: Slot | null = null;
  mostrarPanellAlimentar = false;
  mostrarPanellVacuna = false;

  // Feed / Infecció
  feedResultat: FeedResult | null = null;
  feedCarregant = false;
  feedQuantitat = 1;
  private feedTimer: ReturnType<typeof setTimeout> | null = null;

  // Vacunes
  vacunaSlotSeleccionat: Slot | null = null;
  vacunaCarregant = false;
  vacunaResultat: AplicarVacunaResult | null = null;
  vacunaError: string | null = null;
  private vacunaTimer: ReturnType<typeof setTimeout> | null = null;

  private inventarioService = inject(InventarioService);
  private cdr = inject(ChangeDetectorRef);
  private xuxemonsSub: Subscription;
  private slotsSub: Subscription;
  private evolucioSub: Subscription | null = null;

  get mostrarPaginacio(): boolean {
    return this.getTotalPagines(this.xuxemons$.getValue()) > 1;
  }

  constructor(public xuxemonService: XuxemonService, private router: Router) {
    this.xuxemons$ = this.xuxemonService.xuxemons$;
    this.xuxemonsSub = this.xuxemons$.subscribe((xuxemons) => {
      this.sincronizarSeleccion(xuxemons);
      this.cdr.markForCheck();
    });

    this.inventarioService.cargarInventario();
    this.slotsSub = this.inventarioService.slots$.subscribe(() => {
      this.xuxeEvoSlot = this.getXuxeEvo();
      this.cdr.markForCheck();
    });

    this.xuxemonService.carregarXuxemons('Todos');
  }

  ngOnDestroy(): void {
    this.xuxemonsSub.unsubscribe();
    this.slotsSub.unsubscribe();
    this.evolucioSub?.unsubscribe();
    if (this.feedTimer) clearTimeout(this.feedTimer);
    if (this.vacunaTimer) clearTimeout(this.vacunaTimer);
  }

  cambiarFiltro(tipo: string): void {
    this.filtroActual = tipo;
    this.filtroMida = 'Tots';
    this.paginaActual = 0;
    this.xuxemonSeleccionado = null;
    this.mostrarPanellAlimentar = false;
    this.mostrarPanellVacuna = false;
    this.cerrarEvolucion();
    this.xuxemonService.xuxemons$.next([]);
    this.xuxemonService.carregarXuxemons(tipo);
  }

  canviarMida(mida: string): void {
    this.filtroMida = mida;
    this.paginaActual = 0;
    this.sincronizarSeleccion(this.xuxemons$.getValue());
  }

  getFiltrats(xuxemons: Xuxemon[]): Xuxemon[] {
    return this.xuxemonService.filtrarPerMida(xuxemons, this.filtroMida);
  }

  getPaginats(xuxemons: Xuxemon[]): Xuxemon[] {
    const filtrats = this.getFiltrats(xuxemons);
    const inicio = this.paginaActual * this.itemsPorPagina;
    return filtrats.slice(inicio, inicio + this.itemsPorPagina);
  }

  getPanelEsquerra(xuxemons: Xuxemon[]): Xuxemon[] {
    return this.getPaginats(xuxemons).slice(0, 2);
  }

  getPanelDreta(xuxemons: Xuxemon[]): Xuxemon[] {
    return this.getPaginats(xuxemons).slice(2, 6);
  }

  getTotalPagines(xuxemons: Xuxemon[]): number {
    const filtrats = this.getFiltrats(xuxemons);
    return Math.max(1, Math.ceil(filtrats.length / this.itemsPorPagina));
  }

  paginaAnterior(): void {
    if (this.paginaActual > 0) {
      this.paginaActual--;
      this.sincronizarSeleccion(this.xuxemons$.getValue());
    }
  }

  paginaSiguiente(xuxemons: Xuxemon[]): void {
    if (this.paginaActual < this.getTotalPagines(xuxemons) - 1) {
      this.paginaActual++;
      this.sincronizarSeleccion(this.xuxemons$.getValue());
    }
  }

  seleccionar(xuxemon: Xuxemon): void {
    if (!xuxemon.bloquejat) {
      this.xuxemonSeleccionado = xuxemon;
      this.vacunaSlotSeleccionat = null;
      this.vacunaResultat = null;
      this.vacunaError = null;
      this.mostrarPanellAlimentar = false;
      this.mostrarPanellVacuna = false;
      this.cerrarEvolucion();
    }
  }

  getClassTipus(tipo: string): string {
    const classes: Record<string, string> = {
      Aigua: 'type-agua',
      Terra: 'type-tierra',
      Aire: 'type-aire',
    };
    return classes[tipo] ?? '';
  }

  getClassMida(mida: string): string {
    const classes: Record<string, string> = {
      Petit: 'mida-petit',
      Mitja: 'mida-mitja',
      Gran: 'mida-gran',
    };
    return classes[mida] ?? '';
  }

  getNivell(tamano: string): string {
    const nivells: Record<string, string> = {
      Petit: '★★★',
      Mitja: '★★★★',
      Gran: '★★★★★',
    };
    return nivells[tamano] ?? '★★★';
  }

  sortir(): void {
    this.router.navigate(['/menu-principal']);
  }

  // ── Panells colapsables ───────────────────────────────────────────

  togglePanellAlimentar(): void {
    this.mostrarPanellAlimentar = !this.mostrarPanellAlimentar;
    if (this.mostrarPanellAlimentar) this.mostrarPanellVacuna = false;
  }

  togglePanellVacuna(): void {
    this.mostrarPanellVacuna = !this.mostrarPanellVacuna;
    if (this.mostrarPanellVacuna) this.mostrarPanellAlimentar = false;
  }

  // Retorna la quantitat màxima de xuxes apilables del inventari
  getMaxFeed(): number {
    return this.inventarioService.slots
      .filter(s => !s.empty && s.apilable)
      .reduce((acc, s) => acc + s.cantidad, 0);
  }

  incrementarFeed(): void {
    if (this.feedQuantitat < this.getMaxFeed()) this.feedQuantitat++;
  }

  decrementarFeed(): void {
    if (this.feedQuantitat > 1) this.feedQuantitat--;
  }

  // ── Feed / Infecció ───────────────────────────────────────────────

  alimentar(): void {
    if (!this.xuxemonSeleccionado || this.feedCarregant) return;
    if (this.feedQuantitat < 1) this.feedQuantitat = 1;

    this.feedCarregant = true;
    this.feedResultat = null;

    this.xuxemonService.feed(this.xuxemonSeleccionado.id, this.xuxemonSeleccionado.xuxedex_id || 0, this.feedQuantitat).subscribe({
      next: (res) => {
        this.feedResultat = res;
        this.feedCarregant = false;
        // Recarrega per reflectir la nova malaltia a la targeta i el nou inventari
        this.xuxemonService.carregarXuxemons(this.filtroActual);
        this.inventarioService.cargarInventario();
        // Amaga el toast després de 4 segons
        if (this.feedTimer) clearTimeout(this.feedTimer);
        this.feedTimer = setTimeout(() => {
          this.feedResultat = null;
          this.cdr.markForCheck();
        }, 4000);
        this.cdr.markForCheck();
      },
      error: (err) => {
        this.feedResultat = {
          infectat: false,
          enfermedad: err.error?.enfermedad ?? null,
          message: err.error?.message ?? 'Error en alimentar el Xuxemon.',
          bloquejat: err.error?.bloquejat ?? false,
        };
        this.feedCarregant = false;
        if (this.feedTimer) clearTimeout(this.feedTimer);
        this.feedTimer = setTimeout(() => {
          this.feedResultat = null;
          this.cdr.markForCheck();
        }, 4000);
        this.cdr.markForCheck();
      },
    });
  }

  getEnfermedadIcon(enfermedad: string | null | undefined): string {
    const icons: Record<string, string> = {
      'Bajon de azucar': '🍭',
      'Sobredosis':      '💊',
      'Atracon':         '🤢',
    };
    return enfermedad ? (icons[enfermedad] ?? '🤒') : '';
  }

  // Retorna la classe CSS de l'estat visual de la malaltia per diferenciar estats
  getEnfermedadClass(enfermedad: string | null | undefined): string {
    const classes: Record<string, string> = {
      'Bajon de azucar': 'enfermedad-bajon',
      'Sobredosis':      'enfermedad-sobredosis',
      'Atracon':         'enfermedad-atracon',
    };
    return enfermedad ? (classes[enfermedad] ?? 'enfermedad-generica') : '';
  }

  // Cost en Xuxa EV per evolucionar (3 si té Bajón de azúcar)
  getCostEvolucio(): number {
    return this.xuxemonSeleccionado?.enfermedad === 'Bajon de azucar' ? 3 : 1;
  }

  // Pot evolucionar? (no si té Sobredosis, Atracon, o no té prou Xuxa EV)
  potEvolucionar(): boolean {
    if (this.xuxemonSeleccionado?.enfermedad === 'Sobredosis') return false;
    const cost = this.getCostEvolucio();
    const totalEv = this.inventarioService.slots
      .filter(s => !s.empty && (s.xuxe?.nom ?? s.xuxe?.nombre_xuxes ?? '').trim().toLowerCase() === 'xuxevo')
      .reduce((acc, s) => acc + s.cantidad, 0);
    return totalEv >= cost;
  }

  private sincronizarSeleccion(xuxemons: Xuxemon[]): void {
    const visibles = this.getPaginats(xuxemons);
    if (visibles.length === 0) {
      this.xuxemonSeleccionado = null;
      return;
    }

    const seleccionVisible = visibles.some(
      (xuxemon) => xuxemon.id === this.xuxemonSeleccionado?.id && !xuxemon.bloquejat
    );

    if (seleccionVisible) {
      return;
    }

    this.xuxemonSeleccionado = visibles.find((xuxemon) => !xuxemon.bloquejat) ?? null;
  }

  // ── Vacunes ───────────────────────────────────────────────────────

  // Vacunes que curen la malaltia actual del xuxemon seleccionat
  // Xocolatina → Bajon de azucar | Xal de fruites → Atracon | Inxulina → totes
  getVacunesDisponibles(): Slot[] {
    const enfermedad = this.xuxemonSeleccionado?.enfermedad ?? null;
    const VACUNA_CURA_TOT = 'inxulina';

    const VACUNA_PER_MALALTIA: Record<string, string> = {
      'Bajon de azucar': 'xocolatina',
      'Atracon':         'xal de fruites',
      'Sobredosis':      'inxulina',
    };

    return this.inventarioService.slots.filter(s => {
      if (s.empty || s.apilable) return false;
      const nom = (s.xuxe?.nom ?? s.xuxe?.nombre_xuxes ?? '').trim().toLowerCase();
      if (nom === VACUNA_CURA_TOT) return true;
      if (!enfermedad) return false;
      return nom === VACUNA_PER_MALALTIA[enfermedad];
    });
  }

  // Selecciona o deselecciona un slot de vacuna
  seleccionarVacuna(slot: Slot): void {
    this.vacunaSlotSeleccionat = this.vacunaSlotSeleccionat?.id === slot.id ? null : slot;
    this.vacunaResultat = null;
    this.vacunaError = null;
  }

  // Text que explica què cura cada vacuna
  getVacunaCuraText(slot: Slot): string {
    const nom = (slot.xuxe?.nom ?? slot.xuxe?.nombre_xuxes ?? '').trim().toLowerCase();
    const cures: Record<string, string> = {
      'xocolatina':    'Cura: Bajón de azúcar',
      'xal de fruites': 'Cura: Atracón',
      'inxulina':      'Cura: totes les malalties',
    };
    return cures[nom] ?? 'Vacuna';
  }

  // Aplica la vacuna seleccionada al xuxemon seleccionat
  aplicarVacuna(): void {
    if (!this.xuxemonSeleccionado || !this.vacunaSlotSeleccionat || this.vacunaCarregant) return;
    if (!this.xuxemonSeleccionado.xuxedex_id) return;

    this.vacunaCarregant = true;
    this.vacunaResultat = null;
    this.vacunaError = null;

    // inventario_id és la id del slot real del backend, no el slot local
    // necessitem l'id real de l'item d'inventari → guardat a slot.inventario_id
    const inventarioId = this.vacunaSlotSeleccionat.inventario_id ?? this.vacunaSlotSeleccionat.id;
    this.xuxemonService.aplicarVacuna(
      inventarioId,
      this.xuxemonSeleccionado.xuxedex_id
    ).subscribe({
      next: (res) => {
        this.vacunaResultat = res;
        this.vacunaCarregant = false;
        this.vacunaSlotSeleccionat = null;

        // Actualitza l'inventari i recarrega els xuxemons
        this.inventarioService.cargarInventario();
        this.xuxemonService.carregarXuxemons(this.filtroActual);

        if (this.vacunaTimer) clearTimeout(this.vacunaTimer);
        this.vacunaTimer = setTimeout(() => {
          this.vacunaResultat = null;
          this.cdr.markForCheck();
        }, 4000);
        this.cdr.markForCheck();
      },
      error: (err) => {
        this.vacunaError = err.error?.message ?? 'Error en aplicar la vacuna.';
        this.vacunaCarregant = false;

        if (this.vacunaTimer) clearTimeout(this.vacunaTimer);
        this.vacunaTimer = setTimeout(() => {
          this.vacunaError = null;
          this.cdr.markForCheck();
        }, 4000);
        this.cdr.markForCheck();
      },
    });
  }

  // ── Metodos de Evoluciones de los Xuxemons ────────────────────────

  verCadenaEvolucion(): void {
    if (!this.xuxemonSeleccionado) return;

    this.cargarEvolucion = true;
    this.mostrarEvolucion = true;
    this.errorEvolucion = null;
    this.cadenaEvolucio = [];

    this.evolucioSub?.unsubscribe();
    this.evolucioSub = this.xuxemonService.getEvoluciones(Number(this.xuxemonSeleccionado.id)).subscribe({
      next: (res) => {
        this.cadenaEvolucio = res.cadena_evolutiva;
        this.cargarEvolucion = false;
        this.cdr.markForCheck();
      },
      error: () => {
        this.errorEvolucion = 'No s\'ha pogut carregar la cadena evolutiva.';
        this.cargarEvolucion = false;
        this.cdr.markForCheck();
      },
    });
  }

  cerrarEvolucion(): void {
    this.evolucioSub?.unsubscribe();
    this.evolucioSub = null;
    this.mostrarEvolucion = false;
    this.cargarEvolucion = false;
    this.cadenaEvolucio = [];
    this.errorEvolucion = null;
  }

  getCadenaEvolucioNormalitzada(): EtapaEvoluciones[] {
    const ordre = ['Petit', 'Mitja', 'Gran'];
    return ordre
      .map((tamano) => this.cadenaEvolucio.find((etapa) => etapa.tamano === tamano))
      .filter((etapa): etapa is EtapaEvoluciones => !!etapa);
  }

  PosicionActual(etapa: EtapaEvoluciones): boolean {
    if (!this.xuxemonSeleccionado) return false;

    if (etapa.id === Number(this.xuxemonSeleccionado.id)) {
      return true;
    }

    return etapa.tamano === this.xuxemonSeleccionado.tamano;
  }

  // ── Evolució ─────────────────────────────────────────────────────

  // Retorna el slot que conté una 'Xuxa EV'
  getXuxeEvo(): Slot | null {
    return this.inventarioService.slots.find((s) => {
      if (s.empty || !s.xuxe) return false;

      const xuxeName = (s.xuxe.nom ?? s.xuxe.nombre_xuxes ?? '').trim().toLowerCase();
      return xuxeName === 'xuxevo';
    }) ?? null;
  }

  // Retorna la següent etapa de la cadena (Petit → Mitja → Gran)
  getSegurentEtapa(): EtapaEvoluciones | null {
    if (!this.xuxemonSeleccionado) return null;

    const cadena = this.getCadenaEvolucioNormalitzada();
    if (cadena.length === 0) return null;

    const idx = cadena.findIndex(e => e.id === Number(this.xuxemonSeleccionado?.id));
    if (idx !== -1 && idx + 1 < cadena.length) {
      return cadena[idx + 1];
    }

    // Si no troba per id, busca segons tamano
    const ordre = ['Petit', 'Mitja', 'Gran'];
    const idxTamano = ordre.indexOf(this.xuxemonSeleccionado.tamano);
    if (idxTamano === -1 || idxTamano >= ordre.length - 1) {
      return null;
    }

    return cadena[idxTamano + 1] ?? null;
  }

  // Consumeix una Xuxa EV al backend i desbloqueja la següent etapa
  evolucionar(): void {
    const segurent = this.getSegurentEtapa();
    if (!this.potEvolucionar() || !segurent || !this.xuxemonSeleccionado) return;

    this.xuxemonService.evolucionar(Number(this.xuxemonSeleccionado.id)).subscribe({
      next: () => {
        this.inventarioService.cargarInventario();
        this.xuxemonService.xuxemons$.next([]);
        this.xuxemonService.carregarXuxemons(this.filtroActual);
        this.cerrarEvolucion();
        this.cdr.markForCheck();
      },
      error: (err) => {
        this.errorEvolucion = err.error?.message ?? "Error al evolucionar.";
        this.cdr.markForCheck();
      },
    });
  }
}