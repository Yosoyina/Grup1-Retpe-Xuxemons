import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';

// guard per evitar que un usuari ja logejat accedeixi al login o registre
export const noAuthGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const router = inject(Router);

  // si ja esta autenticat el portem al menu
  if (authService.Autentificacion()) {
    router.navigate(['/menu-principal']);
    return false;
  }

  return true;
};