import { ChangeDetectorRef, Component, OnInit, inject } from '@angular/core';
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
export class MenuPrincipal implements OnInit {
  rewardModalVisible = false;
  dailyReward: DailyRewardResponse | null = null;

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
  }

  closeRewardModal() {
    this.rewardModalVisible = false;
  }

  // Simula una recompensa diària client-side per a testing (no crida el backend)
  simularRecompensa(): void {
    this.dailyReward = {
      status: 'granted',
      granted: true,
      message: 'Simulació de recompensa diària (client-side)',
      available_at: new Date().toISOString(),
      next_available_at: new Date().toISOString(),
      xuxes: [
        { id: 1, nombre_xuxes: 'Piruleta', imagen: null, cantidad: 3, added: 3, discarded: 0 },
        { id: 2, nombre_xuxes: 'Xocolata', imagen: null, cantidad: 2, added: 2, discarded: 0 },
        { id: 3, nombre_xuxes: 'Caramel', imagen: null, cantidad: 5, added: 5, discarded: 0 },
      ],
      xuxes_requested: 10,
      xuxes_added: 10,
      xuxes_discarded: 0,
      xuxemon: null,
      xuxemon_unlocked: false,
    };
    this.rewardModalVisible = true;
    this.cdr.detectChanges();
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
