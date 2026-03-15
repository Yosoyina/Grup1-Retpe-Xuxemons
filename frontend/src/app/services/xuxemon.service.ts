import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { catchError, map, of } from 'rxjs';

export interface EtapaEvoluciones {
  id: number;
  nombre_xuxemon: string;
  tamano: string;
  imagen: string | null;
}


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
  private adminUrl = 'http://localhost:8000/api/admin/xuxedex';

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

  // Obtenir els xuxemons d'un usuari específic (per a admin)
  getAdminXuxedex(userId: number): Observable<Xuxemon[]> {
    return this.http.get<Xuxemon[]>(`${this.adminUrl}?user_id=${userId}`).pipe(
      catchError(() => of([]))
    );
  }

  // Afegir un xuxemon aleatori a un usuari (per a admin)
  addRandomXuxemonToUser(userId: number): Observable<any> {
    return this.http.post(`${this.adminUrl}`, { user_id: userId });
  }

  getEvolucions(id: number): Observable<{ cadena_evolutiva: EtapaEvoluciones[], total_etapes: number }> {
    return this.http.get<{ cadena_evolutiva: EtapaEvoluciones[], total_etapes: number }>(`http://localhost:8000/api/xuxemons/${id}/evolucions`);
  }
}
