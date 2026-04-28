import { ChangeDetectorRef, Component, OnDestroy, OnInit, inject } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { AuthService } from '../services/auth.service';
import { InventarioService } from '../services/inventario.service';
import { DailyRewardResponse, RewardService } from '../services/reward.service';
import { AmicsService } from '../services/amics.service';

@Component({
  selector: 'app-menu-principal',
  imports: [RouterLink, CommonModule],
  templateUrl: './menu-principal.html',
  styleUrl: './menu-principal.css',
})
export class MenuPrincipal implements OnInit, OnDestroy {
  rewardModalVisible = false;
  dailyReward: DailyRewardResponse | null = null;
  nextRewardCountdown = '';

  private countdownIntervalId: ReturnType<typeof setInterval> | null = null;

  // Usem inject() a nivell de camp per poder inicialitzar peticionsCount$ aquí directament
  private amicsService = inject(AmicsService);

  // Observable amb el nombre de sol·licituds d'amistat pendents per al badge del menú
  peticionsCount$ = this.amicsService.peticionsRebudes;

  constructor(
    public authService: AuthService,
    private router: Router,
    private rewardService: RewardService,
    private inventarioService: InventarioService,
    private cdr: ChangeDetectorRef,
  ) {}

  // intentarAutoLogin a app.component ja ha validat el token abans d'arribar aquí.
  // Només cal comprovar la recompensa diària.
  ngOnInit(): void {
    this.checkDailyReward();
    this.amicsService.carregarPeticionsRebudes();
    this.startRewardCountdown();
  }

  ngOnDestroy(): void {
    if (this.countdownIntervalId) {
      clearInterval(this.countdownIntervalId);
    }
  }

  closeRewardModal() {
    this.rewardModalVisible = false;
  }

  openRewardModal(): void {
    if (!this.dailyReward) {
      return;
    }

    this.rewardModalVisible = true;
  }

  hasRewardInfo(): boolean {
    return this.dailyReward !== null;
  }

  getRewardImage(path?: string | null): string {
    return path ? `/${path}` : '/23.webp';
  }

  getRewardTitle(): string {
    return this.dailyReward?.granted ? 'Has recibido tu premio de hoy' : 'Recompensa diaria';
  }

  getRewardMessage(): string {
    if (!this.dailyReward) {
      return '';
    }

    if (this.dailyReward.granted) {
      return this.dailyReward.xuxemon
        ? `Hoy te han tocado ${this.dailyReward.xuxes_added} xuxes y un Xuxemon pequeño nuevo.`
        : `Hoy te han tocado ${this.dailyReward.xuxes_added} xuxes. No hay Xuxemon nuevo porque ya tienes todos los pequeños desbloqueados.`;
    }

    return this.nextRewardCountdown
      ? `Ya has reclamado la recompensa de hoy. La siguiente llega en ${this.nextRewardCountdown}.`
      : 'Ya has reclamado la recompensa de hoy.';
  }

  // Función para cerrar sesión
  logout() {
    this.authService.logout().subscribe({
      next: () => this.router.navigate(['/login']),
      error: () => {
        localStorage.removeItem('token');
        this.router.navigate(['/login']);
      }
    });
  }

  private checkDailyReward() {
    this.rewardService.claimDailyReward().subscribe({
      next: (response) => {
        this.dailyReward = response;
        this.rewardModalVisible = response.granted;
        this.updateRewardCountdown();

        if (response.granted) {
          this.inventarioService.cargarInventario();
        }

        this.cdr.detectChanges();
      },
      error: (error) => {
        console.error('Error obteniendo la recompensa diaria:', error);
        this.cdr.detectChanges();
      }
    });
  }

  private startRewardCountdown(): void {
    this.updateRewardCountdown();

    this.countdownIntervalId = setInterval(() => {
      this.updateRewardCountdown();
      this.cdr.detectChanges();
    }, 60000);
  }

  private updateRewardCountdown(): void {
    if (!this.dailyReward?.next_available_at) {
      this.nextRewardCountdown = '';
      return;
    }

    const nextRewardTime = new Date(this.dailyReward.next_available_at).getTime();
    const remainingMs = nextRewardTime - Date.now();

    if (remainingMs <= 0) {
      this.nextRewardCountdown = 'muy poco';
      return;
    }

    const totalMinutes = Math.floor(remainingMs / 60000);
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    if (hours <= 0) {
      this.nextRewardCountdown = `${minutes} min`;
      return;
    }

    this.nextRewardCountdown = `${hours} h ${minutes} min`;
  }
}
