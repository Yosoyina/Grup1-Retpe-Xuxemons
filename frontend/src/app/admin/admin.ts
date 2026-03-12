import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { XuxemonService, Xuxemon } from '../services/xuxemon.service';

@Component({
  selector: 'app-admin',
  imports: [CommonModule, FormsModule],
  templateUrl: './admin.html',
  styleUrl: './admin.css',
})
export class Admin implements OnInit {
  usuarios: any[] = [];
  usuarioSeleccionado: number | null = null;
  xuxemons: Xuxemon[] = [];
  cargando = false;
  mensajeExito = '';
  mensajeError = '';
  private apiUrl = 'http://localhost:8000/api/admin';

  constructor(
    private http: HttpClient,
    private xuxemonService: XuxemonService
  ) { }

  ngOnInit(): void {
    this.cargarUsuarios();
  }

  cargarUsuarios(): void {
    this.http.get<any[]>(`${this.apiUrl}/usuarios`).subscribe({
      next: (response) => {
        this.usuarios = response;
      },
      error: (err) => {
        console.error('Error cargando usuarios', err);
        this.mensajeError = 'Error al cargar usuarios';
      }
    });
  }

  cargarXuxemonsUsuario(): void {
    if (!this.usuarioSeleccionado) return;

    this.cargando = true;
    this.mensajeExito = '';
    this.mensajeError = '';

    this.xuxemonService.getAdminXuxedex(this.usuarioSeleccionado).subscribe({
      next: (response) => {
        this.xuxemons = response || [];
        this.cargando = false;
      },
      error: (err) => {
        console.error('Error cargando xuxemons', err);
        this.mensajeError = 'Error al cargar Xuxemons';
        this.cargando = false;
      }
    });
  }

  agregarXuxemonAleatorio(): void {
    if (!this.usuarioSeleccionado) return;

    this.cargando = true;
    this.mensajeExito = '';
    this.mensajeError = '';

    this.xuxemonService.addRandomXuxemonToUser(this.usuarioSeleccionado).subscribe({
      next: (response) => {
        this.mensajeExito = `¡${response.xuxemon.nombre_xuxemon} agregado correctamente!`;
        this.cargarXuxemonsUsuario();
        this.cargando = false;
      },
      error: (err) => {
        console.error('Error agregando xuxemon', err);
        if (err.error?.error) {
          this.mensajeError = err.error.error;
        } else {
          this.mensajeError = 'Error al agregar Xuxemon';
        }
        this.cargando = false;
      }
    });
  }

  getNombreUsuario(userId: number): string {
    const usuario = this.usuarios.find(u => u.id === userId);
    return usuario ? `${usuario.nombre} ${usuario.apellidos}` : '';
  }
}
