import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';

// afegeix el token a cada peticio i redirigeix al login si el token es invalid
export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const router = inject(Router);
  const token = localStorage.getItem('token');

  // Si hi ha un token, clona la petició i afegeix l'encapçalament d'autorització
  const reqAmbToken = token
    ? req.clone({ setHeaders: { Authorization: `Bearer ${token}` } })
    : req;

  return next(reqAmbToken).pipe(
    catchError((err: HttpErrorResponse) => {
      if (err.status === 401) {
        localStorage.removeItem('token');
        // No redirigim des d'aquí si és la crida de validació inicial del token
        // (intentarAutoLogin ja gestiona el seu propi error i evita cascada de redirects)
        const isPerfilCall = req.url.includes('/api/perfil');
        if (!isPerfilCall) {
          router.navigate(['/login']);
        }
      }
      return throwError(() => err);
    })
  );
};