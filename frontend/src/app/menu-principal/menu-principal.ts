import { ChangeDetectorRef, Component } from '@angular/core';
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
export class MenuPrincipal {
  carregant = true;
  rewardModalVisible = false;
  dailyReward: DailyRewardResponse | null = null;

  // En el constructor, verificamos que el backend está disponible y que el token es válido
  constructor(
    public authService: AuthService,
    private router: Router,
    private rewardService: RewardService,
    private inventarioService: InventarioService,
    private cdr: ChangeDetectorRef,
  ) {
    // verificar que el backend esta disponible i el token es valid
    this.authService.getPerfil().subscribe({
      next: () => {
        this.checkDailyReward();
      },
      error: () => {
        localStorage.removeItem('token');
        this.router.navigate(['/login']);
      }
    });
  }

  closeRewardModal() {
    this.rewardModalVisible = false;
  }

  getRewardImage(path?: string | null): string {
    return path ? `/${path}` : '/23.png';
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

        this.carregant = false;
        this.cdr.detectChanges();
      },
      error: (error) => {
        console.error('Error obteniendo la recompensa diaria:', error);
        this.carregant = false;
        this.cdr.detectChanges();
      }
    });
  }
}
