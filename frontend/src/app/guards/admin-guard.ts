import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { firstValueFrom } from 'rxjs';

export const adminGuard: CanActivateFn = async (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (!authService.Autentificacion()) {
    router.navigate(['/login']);
    return false;
  }

  if (!authService.usuari$) {
    try {
      await firstValueFrom(authService.getPerfil());
    } catch {
      router.navigate(['/login']);
      return false;
    }
  }

  if (authService.isAdmin()) {
    return true;
  }

  router.navigate(['/menu-principal']);
  return false;
};