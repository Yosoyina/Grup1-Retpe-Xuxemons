import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { API_URL } from '../config/api.config';

export interface UsuarioAdmin {
  id: number;
  nombre: string;
  apellidos: string;
  email: string;
  id_jugador: string | null;
  role: string;
  actiu: boolean;
  avatar: string | null;
}

export interface XuxeItem {
  id: number;
  nombre_xuxes: string;
  apilable: boolean;
  imagen?: string;
}

export interface AdminInventarioItems {
  xuxemons: any[];
  xuxes: XuxeItem[];
}

@Injectable({ providedIn: 'root' })
export class AdminService {
  private adminUrl = `${API_URL}/admin`;

  constructor(private http: HttpClient) {}

  // ── Usuaris ──────────────────────────────────────────────────────────────

  getUsuarios(): Observable<UsuarioAdmin[]> {
    return this.http.get<UsuarioAdmin[]>(`${this.adminUrl}/usuarios`);
  }

  toggleActiu(userId: number): Observable<{ message: string; actiu: boolean }> {
    return this.http.put<{ message: string; actiu: boolean }>(
      `${this.adminUrl}/usuarios/${userId}/toggle`, {}
    );
  }

  toggleRole(userId: number): Observable<{ message: string; role: string }> {
    return this.http.put<{ message: string; role: string }>(
      `${this.adminUrl}/usuarios/${userId}/toggle-role`, {}
    );
  }

  // ── Inventari ─────────────────────────────────────────────────────────────

  getInventarioItems(): Observable<AdminInventarioItems> {
    return this.http.get<AdminInventarioItems>(`${this.adminUrl}/inventario/items`);
  }

  afegirItem(userId: number, xuxeId: number, cantidad: number): Observable<{ mensaje: string; descartado: number; slots_utilizados: number; max_slots: number }> {
    return this.http.post<{ mensaje: string; descartado: number; slots_utilizados: number; max_slots: number }>(
      `${this.adminUrl}/inventario`,
      { user_id: userId, xuxe_id: xuxeId, cantidad }
    );
  }
}
