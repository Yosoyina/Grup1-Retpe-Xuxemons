import { Component } from '@angular/core';
import { CommonModule, AsyncPipe } from '@angular/common';
import { Router } from '@angular/router';
import { XuxemonService, Xuxemon } from '../services/xuxemon.service';
import { BehaviorSubject } from 'rxjs';

@Component({
  selector: 'app-xuxedex',
  standalone: true,
  imports: [CommonModule, AsyncPipe],
  templateUrl: './xuxedex.html',
  styleUrl: './xuxedex.css',
})
export class Xuxedex {
  paginaActual = 0;
  itemsPorPagina = 6;
  filtroActual = 'Todos';
  xuxemonSeleccionado: Xuxemon | null = null;
  xuxemons$: BehaviorSubject<Xuxemon[]>;

  // Paginació només quan el filtre és "Todos"
  get mostrarPaginacio(): boolean {
    return this.filtroActual === 'Todos';
  }

  // Retorna els tipus disponibles per al filtre
  constructor(public xuxemonService: XuxemonService, private router: Router) {
    this.xuxemons$ = this.xuxemonService.xuxemons$;
    this.xuxemonService.carregarXuxemons('Todos');
  }

  // Canvia el filtre de tipus i recarrega els xuxemons
  cambiarFiltro(tipo: string): void {
    this.filtroActual = tipo;
    this.paginaActual = 0;
    this.xuxemonSeleccionado = null;
    this.xuxemonService.xuxemons$.next([]); // Buida la llista mentre carrega els nous xuxemons
    this.xuxemonService.carregarXuxemons(tipo);
  }

  // Retorna els xuxemons paginats segons el filtre actual
  getPaginats(xuxemons: Xuxemon[]): Xuxemon[] {
    if (!this.mostrarPaginacio) return xuxemons; // Tipus concret: tots (sempre 6)
    const inicio = this.paginaActual * this.itemsPorPagina;
    return xuxemons.slice(inicio, inicio + this.itemsPorPagina);
  }

  // Retorna el total de pàgines segons el nombre de xuxemons i els items per pàgina
  getTotalPagines(xuxemons: Xuxemon[]): number {
    return Math.ceil(xuxemons.length / this.itemsPorPagina);
  }

  // Navega a la pàgina anterior si no és la primera
  paginaAnterior(): void {
    if (this.paginaActual > 0) this.paginaActual--;
  }

  // Navega a la pàgina següent si no és l'última
  paginaSiguiente(xuxemons: Xuxemon[]): void {
    if (this.paginaActual < this.getTotalPagines(xuxemons) - 1) this.paginaActual++;
  }

  // Selecciona un xuxemon només si no està bloquejat
  seleccionar(xuxemon: Xuxemon): void {
    if (!xuxemon.bloquejat) this.xuxemonSeleccionado = xuxemon;
  }

  // Retorna la classe CSS segons el tipus de xuxemon per aplicar l'estil corresponent
  getClassTipus(tipo: string): string {
    const classes: Record<string, string> = {
      'Aigua': 'type-agua',
      'Terra': 'type-tierra',
      'Aire': 'type-aire',
    };
    return classes[tipo] ?? '';
  }

  // Navega a la pàgina principal
  sortir(): void {
    this.router.navigate(['/menu-principal']);
  }
}