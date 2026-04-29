import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { API_URL } from '../config/api.config';

export interface RewardXuxe {
  id: number;
  nombre_xuxes: string;
  imagen: string | null;
  cantidad: number;
  added: number;
  discarded: number;
}

export interface RewardXuxemon {
  id: number;
  nombre_xuxemon: string;
  tipo_elemento: string;
  tamano: string;
  descripcio: string | null;
  imagen: string | null;
}

export interface DailyRewardResponse {
  status: 'granted' | 'already_claimed' | 'not_available_yet';
  granted: boolean;
  message: string;
  available_at: string;
  next_available_at: string;
  xuxes: RewardXuxe[];
  xuxes_requested: number;
  xuxes_added: number;
  xuxes_discarded: number;
  xuxemon: RewardXuxemon | null;
  xuxemon_unlocked: boolean;
}

/**
 * Servei de recompensa diària.
 *
 * Crida al backend per reclamar la recompensa diària del jugador
 * i retorna les xuxes i el xuxemon obtinguts (si n'hi ha),
 * així com la data de la pròxima recompensa disponible.
 */
@Injectable({
  providedIn: 'root'
})
export class RewardService {
  private apiUrl = API_URL;

  // ── CONSTRUCTOR ─────────────────────────────────────────────────────────

  constructor(private http: HttpClient) {}

  // ── RECOMPENSA ───────────────────────────────────────────────────────────

  // Reclama la recompensa diària del jugador autenticat
  claimDailyReward(): Observable<DailyRewardResponse> {
    return this.http.post<DailyRewardResponse>(`${this.apiUrl}/reward`, {});
  }
}
