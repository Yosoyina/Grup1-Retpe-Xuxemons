import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';

/**
 * Guard per a rutes públiques.
 *
 * Impedeix que un usuari ja autenticat accedeixi al login o al registre,
 * redirigint-lo directament al menú principal.
 */
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