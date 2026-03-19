import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';

// Guard para proteger rutas de usuarios no autenticados
export const noAuthGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const router = inject(Router);

  // Si el usuario está autenticado, redirige al menú principal
  if (authService.Autentificacion()) {
    router.navigate(['/menu-principal']);
    return false;
  }

  return true;
};