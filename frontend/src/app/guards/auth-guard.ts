import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';

/**
 * Guard d'autenticació.
 *
 * Protegeix les rutes privades: permet l'accés només si l'usuari
 * té un token vàlid dessat. En cas contrari, redirigeix al login.
 */
export const authGuard: CanActivateFn = (_route, _state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  // Si el usuario está autenticado, permite el acceso a la rutas de Menu, Perfil ect...
  if (authService.Autentificacion()) {
    return true;
  }

  // Si no está autenticado, redirige al login
  router.navigate(['/login']);
  return false;
};
