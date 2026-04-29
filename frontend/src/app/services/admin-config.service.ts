import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { API_URL } from '../config/api.config';

export interface SystemConfigItem {
  clave: string;
  valor: string;
  descripcio: string | null;
}

export interface XuxemonNivell {
  id: number;
  nombre_xuxemon: string;
  tipo_elemento: string;
  tamano: string;
  xuxes_per_pujar: number;
  imagen: string | null;
}

/**
 * Servei de configuració global del sistema (admin).
 *
 * Permet llegir i actualitzar els paràmetres de configuració global
 * (hores de recompensa, quantitats diàries, percentatges d'infecció)
 * i els xuxes necessaris per pujar de nivell cada xuxemon.
 */
@Injectable({
  providedIn: 'root',
})
export class AdminConfigService {
  private adminUrl = `${API_URL}/admin`;

  // ── CONSTRUCTOR ─────────────────────────────────────────────────────────

  constructor(private http: HttpClient) {}

  // ── CONFIGURACIÓ GLOBAL ───────────────────────────────────────────────────────

  // Retorna tota la configuració global del sistema
  getConfig(): Observable<SystemConfigItem[]> {
    return this.http.get<SystemConfigItem[]>(`${this.adminUrl}/config`);
  }

  // Actualitza una clau de configuració
  updateConfig(clave: string, valor: number): Observable<any> {
    return this.http.put(`${this.adminUrl}/config/${clave}`, { valor });
  }

  // ── XUXEMONS NIVELL ──────────────────────────────────────────────────────

  // Retorna tots els Xuxemons amb el seu xuxes_per_pujar
  getXuxemonsNivell(): Observable<XuxemonNivell[]> {
    return this.http.get<XuxemonNivell[]>(`${this.adminUrl}/xuxemons-nivell`);
  }

  // Actualitza les xuxes necessàries per fer créixer un xuxemon
  updateXuxesPerPujar(id: number, xuxes_per_pujar: number): Observable<any> {
    return this.http.put(`${this.adminUrl}/xuxemons-nivell/${id}`, { xuxes_per_pujar });
  }
}
