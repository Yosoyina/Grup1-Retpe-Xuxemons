import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { catchError, map, of } from 'rxjs';

export interface Xuxemon {
  id: number | string;
  nombre_xuxemon: string;
  tipo_elemento: 'Aigua' | 'Terra' | 'Aire';
  tamano: string;
  descripcio: string;
  imagen: string | null;
  esta_capturado?: boolean;
  bloquejat?: boolean;
}

@Injectable({
  providedIn: 'root'
})
export class XuxemonService {
  xuxemons$ = new BehaviorSubject<Xuxemon[]>([]);
  private apiUrl = 'http://localhost:8000/api/xuxedex';

  constructor(private http: HttpClient) { }

  // Carrega els xuxemons de la API segons el filtre de tipus
  carregarXuxemons(tipo: string): void {
    const url = tipo === 'Todos' ? this.apiUrl : `${this.apiUrl}?tipo=${tipo}`;

    this.http.get<Xuxemon[]>(url).pipe(
      catchError(() => of([]))
    ).subscribe(llista => {
      // Converteix la ruta relativa de la imatge a URL completa
      const llistaAmbImatges = llista.map(x => ({
        ...x,
        imagen: x.imagen ? `http://localhost:8000/${x.imagen}` : null
      }));
      this.xuxemons$.next(llistaAmbImatges);
    });
  }

  // Filtra la llista per mida localment sense fer cap crida a la API
  filtrarPerMida(xuxemons: Xuxemon[], mida: string): Xuxemon[] {
    if (mida === 'Tots') return xuxemons;
    return xuxemons.filter(x => x.tamano === mida);
  }

  getTipus(): string[] {
    return ['Todos', 'Aigua', 'Terra', 'Aire'];
  }

  getMides(): string[] {
    return ['Tots', 'Petit', 'Mitja', 'Gran'];
  }
}