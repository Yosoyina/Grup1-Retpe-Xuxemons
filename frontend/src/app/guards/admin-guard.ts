import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { firstValueFrom } from 'rxjs';

/**
 * Guard d'administrador.
 *
 * Verifica que l'usuari estigui autenticat i tingui el rol 'admin'.
 * Si el perfil no està carregat, el sol·licita al backend en primer lloc.
 * Si no té permisos, redirigeix al menú principal.
 */
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