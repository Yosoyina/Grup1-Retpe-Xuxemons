import { Component, OnInit } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { AuthService } from './services/auth.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App implements OnInit {
 
  constructor(private authService: AuthService) {}
 
  // En arrencar l'app, intenta restaurar la sessió validant el token al backend.
  // Si el token ha caducat o és invàlid, el neteja i l'authInterceptor
  // redirigirà al login quan el guard rebutgi la ruta.
  ngOnInit(): void {
    this.authService.intentarAutoLogin().subscribe();
  }
}