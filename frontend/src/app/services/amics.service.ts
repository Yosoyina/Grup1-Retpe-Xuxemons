import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';

export interface Amic {
  id: number;
  nombre: string;
  apellidos: string;
  id_jugador: string;
  avatar: string | null;
}

export interface PeticioAmistat {
  id: number;
  id_remitente: number;
  id_destinatario: number;
  estado: 'pendiente' | 'aceptado' | 'rechazado';
  remitente: Amic;
}

@Injectable({
  providedIn: 'root'
})
export class AmicsService {

  private apiUrl = 'http://localhost:8000/api';

  private amics$ = new BehaviorSubject<Amic[]>([]);
  amics = this.amics$.asObservable();

  private peticionsRebudes$ = new BehaviorSubject<PeticioAmistat[]>([]);
  peticionsRebudes = this.peticionsRebudes$.asObservable();

  constructor(private http: HttpClient) {}

  // cerca usuaris per ID de jugador (mínim 3 caràcters)
  cercarUsuaris(q: string): Observable<Amic[]> {
    return this.http.get<Amic[]>(`${this.apiUrl}/users/search`, { params: { q } });
  }

  // carrega la llista d'amics i actualitza el BehaviorSubject
  carregarAmics(): void {
    this.http.get<Amic[]>(`${this.apiUrl}/amigos`).subscribe({
      next: (amics) => this.amics$.next(amics),
    });
  }

  // carrega les peticions rebudes pendents i actualitza el BehaviorSubject
  carregarPeticionsRebudes(): void {
    this.http.get<PeticioAmistat[]>(`${this.apiUrl}/amigos/peticiones-pendientes`).subscribe({
      next: (peticions) => this.peticionsRebudes$.next(peticions),
    });
  }

  // envia una sol·licitud d'amistat
  enviarPeticio(destinatarioId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/amigos/peticion`, { id_destinatario: destinatarioId });
  }

  // accepta una petició d'amistat i recarrega amics i peticions
  acceptarPeticio(id: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/amigos/peticion/${id}/aceptar`, {}).pipe(
      tap(() => {
        this.carregarAmics();
        this.carregarPeticionsRebudes();
      })
    );
  }

  // rebutja una petició d'amistat i recarrega les peticions
  rebutjarPeticio(id: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/amigos/peticion/${id}/rechazar`, {}).pipe(
      tap(() => this.carregarPeticionsRebudes())
    );
  }

  // elimina un amic i recarrega la llista
  eliminarAmic(friendId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/amigos/${friendId}`).pipe(
      tap(() => this.carregarAmics())
    );
  }

  // retorna el valor actual sense subscripció observable
  getAmics(): Amic[] {
    return this.amics$.getValue();
  }

  getPeticionsRebudes(): PeticioAmistat[] {
    return this.peticionsRebudes$.getValue();
  }
}
