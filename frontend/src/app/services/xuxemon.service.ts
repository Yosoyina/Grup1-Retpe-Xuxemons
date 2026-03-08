import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { catchError,map, of } from 'rxjs';

export interface Xuxemon {
  id: number;
  nombre_xuxemon: string;
  tipo_elemento: 'Aigua' | 'Terra' | 'Aire';
  tamano: string;
  descripcio: string;
  imagen: string;
  esta_capturado?: boolean;
  bloquejat?: boolean;
}

@Injectable({
  providedIn: 'root'
})
export class XuxemonService {
  public xuxemonesFiltrados$ = new BehaviorSubject<Xuxemon[]>([]);
  private apiUrl = 'http://localhost:8000/api/xuxedex';
  private storageUrl = 'http://localhost:8000';


  constructor(private http: HttpClient) { }

  getXuxemonesFiltrados(): Observable<Xuxemon[]> {
    return this.xuxemonesFiltrados$.asObservable();
  }

  private resolverImagen(imagen: string | null): string | null {
    if (!imagen) return null;
    // Si ja és URL absoluta, la deixem tal qual
    if (imagen.startsWith('http')) return imagen;
    // Construïm la URL completa apuntant al storage de Laravel
    return `${this.storageUrl}/${imagen}`;
  }

  aplicarFiltro(tipo: string): void {
    const url = tipo === 'Todos'
      ? this.apiUrl
      : `${this.apiUrl}?tipo=${tipo}`;

    this.http.get<any[]>(url).pipe(
      map(xuxemons => xuxemons.map(x => ({
        ...x,
        imagen: this.resolverImagen(x.imagen),
        esta_capturado: x.esta_capturado === 1 || x.esta_capturado === true, 
        bloquejat: x.bloquejat === true,
      }))),
      catchError((error) => {
        console.error('Error al obtenir Xuxemons:', error);
        return of([]);
      })
    ).subscribe((xuxemons) => this.xuxemonesFiltrados$.next(xuxemons));
  }

  getTiposElementos(): string[] {
    return ['Todos', 'Aigua', 'Terra', 'Aire'];
  }
}
