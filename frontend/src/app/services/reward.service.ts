import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';

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

const REWARD_CACHE_KEY = 'daily_reward_cache';

@Injectable({
  providedIn: 'root'
})
export class RewardService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  claimDailyReward(): Observable<DailyRewardResponse> {
    return this.http.post<DailyRewardResponse>(`${this.apiUrl}/reward`, {}).pipe(
      tap((response) => {
        if (response.status === 'granted' || response.status === 'already_claimed') {
          this.saveCachedReward(response);
          return;
        }

        if (response.status === 'not_available_yet') {
          this.clearCachedRewardIfExpired(response.available_at);
        }
      })
    );
  }

  getCachedReward(): DailyRewardResponse | null {
    const raw = localStorage.getItem(REWARD_CACHE_KEY);
    if (!raw) return null;

    try {
      const parsed = JSON.parse(raw) as DailyRewardResponse;

      if (parsed.status !== 'granted' && parsed.status !== 'already_claimed') {
        localStorage.removeItem(REWARD_CACHE_KEY);
        return null;
      }

      const nextAvailableAt = new Date(parsed.next_available_at).getTime();
      if (Number.isNaN(nextAvailableAt) || Date.now() >= nextAvailableAt) {
        localStorage.removeItem(REWARD_CACHE_KEY);
        return null;
      }

      return parsed;
    } catch {
      localStorage.removeItem(REWARD_CACHE_KEY);
      return null;
    }
  }

  private saveCachedReward(reward: DailyRewardResponse) {
    localStorage.setItem(REWARD_CACHE_KEY, JSON.stringify(reward));
  }

  private clearCachedRewardIfExpired(availableAt: string) {
    const cachedReward = this.getCachedReward();
    if (!cachedReward) return;

    const availableTime = new Date(availableAt).getTime();
    const cachedNextTime = new Date(cachedReward.next_available_at).getTime();

    if (!Number.isNaN(availableTime) && !Number.isNaN(cachedNextTime) && availableTime >= cachedNextTime) {
      localStorage.removeItem(REWARD_CACHE_KEY);
    }
  }
}