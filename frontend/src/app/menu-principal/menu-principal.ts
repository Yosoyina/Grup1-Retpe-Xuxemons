import { ChangeDetectorRef, Component, OnDestroy } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { AuthService } from '../services/auth.service';
import { InventarioService } from '../services/inventario.service';
import { DailyRewardResponse, RewardService } from '../services/reward.service';

@Component({
  selector: 'app-menu-principal',
  imports: [RouterLink, CommonModule],
  templateUrl: './menu-principal.html',
  styleUrl: './menu-principal.css',
})
export class MenuPrincipal implements OnDestroy {
  carregant = true;
  rewardModalVisible = false;
  dailyReward: DailyRewardResponse | null = null;
  countdownText = '';

  private countdownIntervalId: ReturnType<typeof setInterval> | null = null;

  constructor(
    public authService: AuthService,
    private router: Router,
    private rewardService: RewardService,
    private inventarioService: InventarioService,
    private cdr: ChangeDetectorRef,
  ) {
    const cachedReward = this.rewardService.getCachedReward();
    if (cachedReward) {
      this.dailyReward = cachedReward;
      this.startCountdown(cachedReward.next_available_at);
    }

    this.authService.getPerfil().subscribe({
      next: () => {
        const cachedReward = this.rewardService.getCachedReward();
        if (cachedReward) {
          this.dailyReward = cachedReward;
          this.startCountdown(cachedReward.next_available_at);
        }

        this.checkDailyReward();
      },
      error: () => {
        localStorage.removeItem('token');
        this.router.navigate(['/login']);
      }
    });
  }

  get hasRewardHistory(): boolean {
    return !!this.dailyReward && (
      this.dailyReward.status === 'granted' ||
      this.dailyReward.status === 'already_claimed'
    );
  }

  get rewardModalTitle(): string {
    if (!this.dailyReward) return 'Recompensa diaria';

    return this.dailyReward.status === 'already_claimed'
      ? 'Recompensa diaria ya entregada'
      : 'Has recibido tu premio de hoy';
  }

  get rewardModalMessage(): string {
    if (!this.dailyReward) return '';

    if (this.dailyReward.status === 'already_claimed') {
      return 'Esta es la recompensa que ya has recibido hoy. La siguiente llega siempre a las 08:00.';
    }

    if (this.dailyReward.xuxemon) {
      return `Hoy te han tocado ${this.dailyReward.xuxes_added} xuxes y un Xuxemon pequeño nuevo.`;
    }

    return `Hoy te han tocado ${this.dailyReward.xuxes_added} xuxes. No hay Xuxemon nuevo porque ya tienes todos los pequeños desbloqueados.`;
  }

  closeRewardModal() {
    this.rewardModalVisible = false;
  }

  openRewardModal() {
    if (!this.dailyReward) return;
    this.rewardModalVisible = true;
  }

  getRewardImage(path?: string | null): string {
    return path ? `/${path}` : '/23.png';
  }

  logout() {
    this.authService.logout().subscribe({
      next: () => this.router.navigate(['/login']),
      error: () => {
        localStorage.removeItem('token');
        this.router.navigate(['/login']);
      }
    });
  }

  ngOnDestroy(): void {
    this.stopCountdown();
  }

  private checkDailyReward() {
    this.rewardService.claimDailyReward().subscribe({
      next: (response) => {
        this.dailyReward = response;
        this.rewardModalVisible = response.granted;
        this.startCountdown(response.next_available_at);

        if (response.granted) {
          this.inventarioService.cargarInventario();
        }

        this.carregant = false;
        this.cdr.detectChanges();
      },
      error: (error) => {
        console.error('Error obteniendo la recompensa diaria:', error);

        const cachedReward = this.rewardService.getCachedReward();
        if (cachedReward) {
          this.dailyReward = cachedReward;
          this.startCountdown(cachedReward.next_available_at);
        } else {
          this.stopCountdown();
        }

        this.carregant = false;
        this.cdr.detectChanges();
      }
    });
  }

  private startCountdown(nextAvailableAt: string) {
    this.stopCountdown();

    const updateCountdown = () => {
      const target = new Date(nextAvailableAt).getTime();
      const now = Date.now();
      const diff = Math.max(0, target - now);
      const totalSeconds = Math.floor(diff / 1000);

      const hours = Math.floor(totalSeconds / 3600);
      const minutes = Math.floor((totalSeconds % 3600) / 60);
      const seconds = totalSeconds % 60;

      this.countdownText =
        `${hours.toString().padStart(2, '0')}:` +
        `${minutes.toString().padStart(2, '0')}:` +
        `${seconds.toString().padStart(2, '0')}`;
    };

    updateCountdown();

    this.countdownIntervalId = setInterval(() => {
      updateCountdown();
      this.cdr.markForCheck();
    }, 1000);
  }

  private stopCountdown() {
    if (this.countdownIntervalId) {
      clearInterval(this.countdownIntervalId);
      this.countdownIntervalId = null;
    }
  }
}