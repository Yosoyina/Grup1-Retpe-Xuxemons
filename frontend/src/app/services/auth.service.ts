import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject, tap, map, catchError, of } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  private apiUrl = 'http://localhost:8000/api';

  // BehaviorSubject que guarda l'usuari actual (null si no esta logejat)
  private usuariActual = new BehaviorSubject<any>(null);

  // observable public perque els components puguin escoltar els canvis
  usuari$ = this.usuariActual.asObservable();

  constructor(private http: HttpClient) { }

  register(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/register`, data).pipe(
      tap((response: any) => localStorage.setItem('token', response.token))
    );
  }

  // envia les dades de login al backend i guarda el token si tot va be
  login(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, data).pipe(
      tap((response: any) => {
        localStorage.setItem('token', response.token);
        // guardem l'usuari al BehaviorSubject despres del login
        this.usuariActual.next(response.user);
      })
    );
  }

  // obte les dades del perfil de l'usuari autenticat
  getPerfil(): Observable<any> {
    return this.http.get(`${this.apiUrl}/profile`).pipe(
      tap((response: any) => {
        // actualitzem el BehaviorSubject amb les dades del perfil
        this.usuariActual.next(response.user);
      })
    );
  }

  // actualitza les dades del perfil
  updatePerfil(data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/profile`, data).pipe(
      tap((response: any) => {
        // actualitzem el BehaviorSubject amb les dades del perfil actualitzat
        this.usuariActual.next(response.user);
      })
    );
  }

  // elimina el compte de l'usuari
  eliminarCompte(): Observable<any> {
    return this.http.delete(`${this.apiUrl}/profile`);
  }

  // tanca la sessio i esborra el token
  logout(): Observable<any> {
    return this.http.post(`${this.apiUrl}/logout`, {}).pipe(
      tap(() => {
        localStorage.removeItem('token');
        // netegem l'usuari del BehaviorSubject al logout
        this.usuariActual.next(null);
      })
    );
  }

  // Retorna el valor actual de l'usuari sense fer cap petició HTTP
  getUsuariActual(): any {
    return this.usuariActual.getValue();
  }

  // Comprobacion de si el usuario está autenticado
  Autentificacion(): boolean {
    return !!localStorage.getItem('token');
  }

  // Comprobacion de si el usuario es administrador
  isAdmin(): boolean {
    const usuario = this.usuariActual.getValue();
    return usuario && usuario.role === 'admin';
  }

  // Intenta recuperar la sessió existent validant el token al backend.
  // Si el token és vàlid, carrega l'usuari al BehaviorSubject i retorna true.
  // Si és invàlid o ha caducat, neteja el token i retorna false.
  intentarAutoLogin(): Observable<boolean> {
    if (!localStorage.getItem('token')) {
      return of(false);
    }

    return this.getPerfil().pipe(
      map(() => true),
      catchError(() => {
        localStorage.removeItem('token');
        this.usuariActual.next(null);
        return of(false);
      })
    );
  }

}