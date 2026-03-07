import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';

export interface Xuxemon {
  id: number;
  nombre_xuxemon: string;
  tipo_elemento: 'Aigua' | 'Terra' | 'Aire';
  tamano: string;
  descripcio: string;
  imagen: string;
  nivel?: number;
  experiencia?: number;
  esta_capturado?: boolean;
}

@Injectable({
  providedIn: 'root'
})
export class XuxemonService {
  public xuxemonesFiltrados$ = new BehaviorSubject<Xuxemon[]>([]);
  private apiUrl = 'http://localhost:8000/api/xuxedex';

  constructor(private http: HttpClient) {}

  getXuxemonesFiltrados(): Observable<Xuxemon[]> {
    return this.xuxemonesFiltrados$.asObservable();
  }

  aplicarFiltro(tipo: string): void {
    const url = tipo === 'Todos' 
      ? this.apiUrl
      : `${this.apiUrl}?tipo=${tipo}`;

    this.http.get<Xuxemon[]>(url).subscribe(
      (xuxemons) => this.xuxemonesFiltrados$.next(xuxemons),
      (error) => console.error('Error al obtener Xuxemons:', error)
    );
  }

  getTiposElementos(): string[] {
    return ['Todos', 'Aigua', 'Terra', 'Aire'];
  }
}
