import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { XuxemonService, Xuxemon } from '../services/xuxemon.service';

interface UsuarioAdmin {
  id: number;
  nombre: string;
  apellidos: string;
  email: string;
  id_jugador: string | null;
  role: string;
  actiu: boolean;
}

@Component({
  selector: 'app-admin',
  imports: [CommonModule],
  templateUrl: './admin.html',
  styleUrl: './admin.css',
})
export class Admin implements OnInit {
  usuarios: UsuarioAdmin[] = [];
  usuarioSeleccionado: number | null = null;
  xuxemons: Xuxemon[] = [];
  cargandoUsuarios = false;
  cargandoXuxemons = false;
  agregandoParaUsuarioId: number | null = null;
  mensajeExito = '';
  mensajeError = '';
  modalAbierto = false;
  usuarioAConfirmar: UsuarioAdmin | null = null;
  private apiUrl = 'http://localhost:8000/api/admin';

  // Getters para filtrar xuxemons por tipo
  get xuxemonsAgua(): Xuxemon[] {
    return this.xuxemons.filter(x => x.tipo_elemento === 'Aigua');
  }

  // Getters para filtrar xuxemons por tipo
  get xuxemonsAire(): Xuxemon[] {
    return this.xuxemons.filter(x => x.tipo_elemento === 'Aire');
  }

  // Getters para filtrar xuxemons por tipo
  get xuxemonsTerra(): Xuxemon[] {
    return this.xuxemons.filter(x => x.tipo_elemento === 'Terra');
  }

  // Getters para filtrar xuxemons por tipo
  constructor(
    private http: HttpClient,
    private xuxemonService: XuxemonService,
    private cdr: ChangeDetectorRef,
    private router: Router
  ) { }

  // Al cargar el componente, obtenemos la lista de usuarios
  ngOnInit(): void {
    this.cargarUsuarios();
  }

  // Al cargar el componente, obtenemos la lista de usuarios
  cargarUsuarios(): void {
    this.cargandoUsuarios = true;

    this.http.get<any>(`${this.apiUrl}/usuarios`).subscribe({
      next: (response) => {
        this.usuarios = Array.isArray(response)
          ? response
          : (response?.users ?? response?.data ?? []);
        this.cargandoUsuarios = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        console.error('Error cargando usuarios', err);
        if (err.status === 401) {
          this.mensajeError = 'Sesion caducada. Vuelve a iniciar sesion.';
        } else if (err.status === 403) {
          this.mensajeError = 'No tienes permisos de admin para ver usuarios.';
        } else {
          this.mensajeError = 'Error al cargar usuarios';
        }
        this.cargandoUsuarios = false;
        this.cdr.detectChanges();
      }
    });
  }

  // Al hacer click en "Ver Xuxemons", cargamos los xuxemons del usuario seleccionado
  cargarXuxemonsUsuario(userId: number): void {
    this.usuarioSeleccionado = userId;
    this.modalAbierto = true;
    this.xuxemons = [];
    this.cargandoXuxemons = true;
    this.mensajeExito = '';
    this.mensajeError = '';

    this.xuxemonService.getAdminXuxedex(userId).subscribe({
      next: (response) => {
        this.xuxemons = (response || []).map(x => ({
          ...x,
          imagen: x.imagen ? `http://localhost:8000/${x.imagen}` : null
        }));
        this.cargandoXuxemons = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        console.error('Error cargando xuxemons', err);
        this.mensajeError = 'Error al cargar Xuxemons';
        this.cargandoXuxemons = false;
        this.cdr.detectChanges();
      }
    });
  }

  // Al hacer click en "Agregar Xuxemon Aleatorio", se agrega un xuxemon aleatorio al usuario seleccionado
  agregarXuxemonAleatorio(userId: number): void {
    this.agregandoParaUsuarioId = userId;
    this.mensajeExito = '';
    this.mensajeError = '';

    this.xuxemonService.addRandomXuxemonToUser(userId).subscribe({
      next: (response) => {
        this.mensajeExito = `¡${response.xuxemon.nombre_xuxemon} agregado correctamente!`;
        if (this.usuarioSeleccionado === userId) {
          this.cargarXuxemonsUsuario(userId);
        }
        this.agregandoParaUsuarioId = null;
      },
      error: (err) => {
        console.error('Error agregando xuxemon', err);
        if (err.error?.error) {
          this.mensajeError = err.error.error;
        } else {
          this.mensajeError = 'Error al agregar Xuxemon';
        }
        this.agregandoParaUsuarioId = null;
      }
    });
  }

  // Cierra el modal de xuxemons
  cerrarModal(): void {
    this.modalAbierto = false;
    this.usuarioSeleccionado = null;
    this.xuxemons = [];
  }

  // Al hacer click en "Habilitar/Deshabilitar", se muestra un modal de confirmación
  pedirConfirmacionToggle(usuario: UsuarioAdmin): void {
    this.usuarioAConfirmar = usuario;
  }

  // Cancela la acción de habilitar/deshabilitar y cierra el modal de confirmación
  cancelarConfirmacion(): void {
    this.usuarioAConfirmar = null;
  }

  // Confirma la acción de habilitar/deshabilitar y actualiza el estado del usuario
  confirmarToggle(): void {
    if (!this.usuarioAConfirmar) return;
    const usuario = this.usuarioAConfirmar;
    this.usuarioAConfirmar = null;

    this.http.put<{ message: string; actiu: boolean }>(
      `${this.apiUrl}/usuarios/${usuario.id}/toggle`, {}
    ).subscribe({
      next: (res) => {
        usuario.actiu = res.actiu;
        this.mensajeExito = res.message;
        this.mensajeError = '';
        this.cdr.detectChanges();
      },
      error: (err) => {
        console.error('Error toggling usuario', err);
        this.mensajeError = 'Error al cambiar el estado del usuario';
        this.cdr.detectChanges();
      }
    });
  }

  // Función para obtener el nombre completo del usuario a partir de su ID
  getNombreUsuario(userId: number): string {
    const usuario = this.usuarios.find(u => u.id === userId);
    return usuario ? `${usuario.nombre} ${usuario.apellidos}` : '';
  }


  // Función para obtener las iniciales del usuario a partir de su nombre y apellidos
  getIniciales(nombre: string, apellidos: string): string {
    const n = (nombre || '').trim().charAt(0);
    const a = (apellidos || '').trim().charAt(0);
    return `${n}${a}`.toUpperCase();
  }

  // Función para salir del panel de administración y volver al menú principal
  salir(): void {
    this.router.navigate(['/menu-principal']);
  }
}
