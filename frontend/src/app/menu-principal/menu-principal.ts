import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
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
export class MenuPrincipal implements OnInit {
  rewardModalVisible = false;
  dailyReward: DailyRewardResponse | null = null;

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
  }

  closeRewardModal() {
    this.rewardModalVisible = false;
  }

  getRewardImage(path?: string | null): string {
    return path ? `/${path}` : '/23.webp';
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
}
