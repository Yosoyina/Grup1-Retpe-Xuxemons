import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { XuxemonService, Xuxemon } from '../services/xuxemon.service';

@Component({
  selector: 'app-xuxedex',
  standalone: true,
  imports: [CommonModule],
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

  constructor(
    private xuxemonService: XuxemonService,
    private router: Router
  ) {
    this.xuxemonService.aplicarFiltro('Todos');
  }

  cambiarFiltro(tipo: string): void {
    this.filtroActual = tipo;
    this.paginaActual = 0;
    this.xuxemonService.aplicarFiltro(tipo);
  }

  getXuxemonesPaginados(xuxemons: Xuxemon[]): Xuxemon[] {
    const inicio = this.paginaActual * this.itemsPorPagina;
    return xuxemons.slice(inicio, inicio + this.itemsPorPagina);
  }

  paginaAnterior(xuxemons: Xuxemon[]): void {
    const maxPaginas = Math.ceil(xuxemons.length / this.itemsPorPagina);
    if (this.paginaActual > 0) {
      this.paginaActual--;
    }
  }

  paginaSiguiente(xuxemons: Xuxemon[]): void {
    const maxPaginas = Math.ceil(xuxemons.length / this.itemsPorPagina);
    if (this.paginaActual < maxPaginas - 1) {
      this.paginaActual++;
    }
  }

  seleccionarXuxemon(xuxemon: Xuxemon): void {
    this.xuxemonSeleccionado = xuxemon;
  }

  getClaseTipo(tipo: string): string {
    const clases: { [key: string]: string } = {
      'Aigua': 'type-agua',
      'Terra': 'type-tierra',
      'Aire': 'type-aire'
    };
    return clases[tipo] || '';
  }

  estaCapturaado(xuxemon: Xuxemon): boolean {
    return xuxemon.esta_capturado ?? false;
  }

  sortir(): void {
    this.router.navigate(['/menu-principal']);
  }
}
