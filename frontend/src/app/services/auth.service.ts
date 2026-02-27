import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) { }

  register(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/register`, data).pipe(
      tap((response: any) => localStorage.setItem('token', response.token))
    );
  }

  // envia les dades de login al backend i guarda el token si tot va be
  login(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, data).pipe(
      tap((response: any) => localStorage.setItem('token', response.token))
    );
  }

  // obte les dades del perfil de l'usuari autenticat
  getPerfil(): Observable<any> {
    return this.http.get(`${this.apiUrl}/profile`);
  }

  // actualitza les dades del perfil
  updatePerfil(data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/profile`, data);
  }

  // elimina el compte de l'usuari
  eliminarCompte(): Observable<any> {
    return this.http.delete(`${this.apiUrl}/profile`);
  }

  // tanca la sessio i esborra el token
  logout(): Observable<any> {
    return this.http.post(`${this.apiUrl}/logout`, {}).pipe(
      tap(() => localStorage.removeItem('token'))
    );
  }
}