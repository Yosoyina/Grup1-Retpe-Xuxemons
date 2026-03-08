import { Component } from '@angular/core';
import { CommonModule, AsyncPipe } from '@angular/common';
import { Router } from '@angular/router';
import { XuxemonService, Xuxemon } from '../services/xuxemon.service';

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

  get xuxemonesFiltrados$() {
    return this.xuxemonService.xuxemonesFiltrados$;
  }

  get tiposElementos(): string[] {
    return this.xuxemonService.getTiposElementos();
  }

  // Si el filtre és un tipus concret, no cal paginació (sempre 6 cartes = 1 pàgina)
  get mostrarPaginacio(): boolean {
    return this.filtroActual === 'Todos';
  }

  constructor(
    private xuxemonService: XuxemonService,
    private router: Router
  ) {
    this.xuxemonService.aplicarFiltro('Todos');
  }

  cambiarFiltro(tipo: string): void {
    this.filtroActual = tipo;
    this.paginaActual = 0;
    this.xuxemonSeleccionado = null;
    this.xuxemonService.aplicarFiltro(tipo);
  }

  getXuxemonesPaginados(xuxemons: Xuxemon[]): Xuxemon[] {
    if (!this.mostrarPaginacio) return xuxemons; // Tipus concret: tots (sempre 6)
    const inicio = this.paginaActual * this.itemsPorPagina;
    return xuxemons.slice(inicio, inicio + this.itemsPorPagina);
  }

  getTotalPagines(xuxemons: Xuxemon[]): number {
    return Math.ceil(xuxemons.length / this.itemsPorPagina);
  }

  paginaAnterior(xuxemons: Xuxemon[]): void {
    if (this.paginaActual > 0) this.paginaActual--;
  }

  paginaSiguiente(xuxemons: Xuxemon[]): void {
    if (this.paginaActual < this.getTotalPagines(xuxemons) - 1) this.paginaActual++;
  }

  seleccionarXuxemon(xuxemon: Xuxemon): void {
    if (!xuxemon.bloquejat) this.xuxemonSeleccionado = xuxemon;
  }

  getClaseTipo(tipo: string): string {
    const clases: { [key: string]: string } = {
      'Aigua': 'type-agua',
      'Terra': 'type-tierra',
      'Aire': 'type-aire'
    };
    return clases[tipo] || '';
  }

  onImageError(event: Event): void {
    const img = event.target as HTMLImageElement;
    img.style.display = 'none';
    const placeholder = document.createElement('div');
    placeholder.className = 'placeholder-img';
    placeholder.textContent = img.alt?.charAt(0) ?? '?';
    img.parentNode?.insertBefore(placeholder, img);
  }

  sortir(): void {
    this.router.navigate(['/menu-principal']);
  }
}