import { Component, OnDestroy, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule, AsyncPipe } from '@angular/common';
import { Router } from '@angular/router';
import { BehaviorSubject, Subscription } from 'rxjs';
import { XuxemonService, Xuxemon, EtapaEvoluciones, FeedResult } from '../services/xuxemon.service';
import { InventarioService, Slot } from '../services/inventario.service';

@Component({
  selector: 'app-xuxedex',
  standalone: true,
  imports: [CommonModule, AsyncPipe],
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

  // Feed / Infecció
  feedResultat: FeedResult | null = null;
  feedCarregant = false;
  private feedTimer: ReturnType<typeof setTimeout> | null = null;

  private inventarioService = inject(InventarioService);
  private cdr = inject(ChangeDetectorRef);
  private xuxemonsSub: Subscription;
  private slotsSub: Subscription;
  private evolucioSub: Subscription | null = null;

  get mostrarPaginacio(): boolean {
    return this.filtroActual === 'Todos';
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
  }

  cambiarFiltro(tipo: string): void {
    this.filtroActual = tipo;
    this.filtroMida = 'Tots';
    this.paginaActual = 0;
    this.xuxemonSeleccionado = null;
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
    if (!this.mostrarPaginacio) {
      return filtrats.slice(0, this.itemsPorPagina);
    }

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

  // ── Feed / Infecció ───────────────────────────────────────────────

  alimentar(): void {
    if (!this.xuxemonSeleccionado || this.feedCarregant) return;

    this.feedCarregant = true;
    this.feedResultat = null;

    this.xuxemonService.feed(this.xuxemonSeleccionado.id).subscribe({
      next: (res) => {
        this.feedResultat = res;
        this.feedCarregant = false;
        // Recarrega per reflectir la nova malaltia a la targeta
        this.xuxemonService.carregarXuxemons(this.filtroActual);
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

  // Metodos de Evoluciones de los Xuxemons

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
      return xuxeName === 'xuxa ev';
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
    const slot = this.xuxeEvoSlot ?? this.getXuxeEvo();
    const segurent = this.getSegurentEtapa();
    if (!slot || !segurent || !this.xuxemonSeleccionado) return;

    this.xuxemonService.evolucionar(Number(this.xuxemonSeleccionado.id)).subscribe({
      next: () => {
        this.inventarioService.EliminarXuxesinv(slot.id);
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