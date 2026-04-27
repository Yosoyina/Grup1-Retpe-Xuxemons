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

@Injectable({
  providedIn: 'root'
})
export class RewardService {
  private apiUrl = API_URL;

  constructor(private http: HttpClient) {}

  claimDailyReward(): Observable<DailyRewardResponse> {
    return this.http.post<DailyRewardResponse>(`${this.apiUrl}/reward`, {});
  }
}
