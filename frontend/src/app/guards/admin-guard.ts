import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { firstValueFrom } from 'rxjs';

// Guard para proteger rutas de administrador
export const adminGuard: CanActivateFn = async (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  // Verifica si el usuario está autenticado
  if (!authService.Autentificacion()) {
    router.navigate(['/login']);
    return false;
  }

  // Si el perfil del usuario no está cargado, intenta cargarlo
  if (!authService.getUsuariActual()) {
    try {
      await firstValueFrom(authService.getPerfil());
    } catch {
      router.navigate(['/login']);
      return false;
    }
  }

  // Verifica si el usuario tiene rol de administrador
  if (authService.isAdmin()) {
    return true;
  }

  router.navigate(['/menu-principal']);
  return false;
};