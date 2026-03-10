import { Component, OnDestroy } from '@angular/core';
import { CommonModule, AsyncPipe } from '@angular/common';
import { Router } from '@angular/router';
import { BehaviorSubject, Subscription } from 'rxjs';
import { XuxemonService, Xuxemon } from '../services/xuxemon.service';

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

  private xuxemonsSub: Subscription;

  get mostrarPaginacio(): boolean {
    return this.filtroActual === 'Todos';
  }

  constructor(public xuxemonService: XuxemonService, private router: Router) {
    this.xuxemons$ = this.xuxemonService.xuxemons$;
    this.xuxemonsSub = this.xuxemons$.subscribe((xuxemons) => {
      this.sincronizarSeleccion(xuxemons);
    });
    this.xuxemonService.carregarXuxemons('Todos');
  }

  ngOnDestroy(): void {
    this.xuxemonsSub.unsubscribe();
  }

  cambiarFiltro(tipo: string): void {
    this.filtroActual = tipo;
    this.filtroMida = 'Tots';
    this.paginaActual = 0;
    this.xuxemonSeleccionado = null;
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
}
