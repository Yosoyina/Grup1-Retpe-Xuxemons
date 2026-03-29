import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { XuxemonService, Xuxemon } from '../services/xuxemon.service';
import { AdminConfigService, SystemConfigItem, XuxemonNivell } from '../services/admin-config.service';
import { finalize } from 'rxjs';

interface UsuarioAdmin {
  id: number;
  nombre: string;
  apellidos: string;
  email: string;
  id_jugador: string | null;
  role: string;
  actiu: boolean;
  avatar: string | null;
}

interface XuxeItem {
  id: number;
  nombre_xuxes: string;
  apilable: boolean;
  imagen?: string;
}

@Component({
  selector: 'app-admin',
  imports: [CommonModule, FormsModule],
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

  // Inventari
  xuxesDisponibles: XuxeItem[] = [];
  xuxesQuantitats: Record<number, number> = {};
  modalInventariAbierto = false;
  usuarioInventariId: number | null = null;
  afegindoXuxes = false;

  // Configuració global del sistema
  configItems: SystemConfigItem[] = [];
  configEdits: Record<string, number> = {};
  guardantConfig: Record<string, boolean> = {};
  configMissatgeExito = '';
  configMissatgeError = '';

  // Xuxemons nivell (xuxes per pujar)
  xuxemonsNivell: XuxemonNivell[] = [];
  xuxesPerPujarEdits: Record<number, number> = {};
  guardantNivell: Record<number, boolean> = {};
  nivellMissatgeExito = '';
  nivellMissatgeError = '';

  // Pestanya activa ('usuaris', 'config', 'xuxemons')
  pestanyaActiva: string = 'usuaris';

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
    private adminConfigService: AdminConfigService,
    private cdr: ChangeDetectorRef,
    private router: Router
  ) { }

  // Al cargar el componente, obtenemos la lista de usuarios
  ngOnInit(): void {
    this.cargarUsuarios();
    this.cargarXuxesDisponibles();
    this.cargarConfig();
    this.cargarXuxemonsNivell();
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
  cargarXuxemonsUsuario(userId: number, preserveMessages = false): void {
    this.usuarioSeleccionado = userId;
    this.modalAbierto = true;
    this.xuxemons = [];
    this.cargandoXuxemons = true;
    if (!preserveMessages) {
      this.mensajeExito = '';
      this.mensajeError = '';
    }

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

    this.xuxemonService.addRandomXuxemonToUser(userId)
      .pipe(
        finalize(() => {
          this.agregandoParaUsuarioId = null;
          this.cdr.detectChanges();
        })
      )
      .subscribe({
        next: (response) => {
          this.mensajeExito = `¡${response.xuxemon.nombre_xuxemon} agregado correctamente!`;
          this.mensajeError = '';
          if (this.usuarioSeleccionado === userId) {
            this.cargarXuxemonsUsuario(userId, true);
          }
          this.cdr.detectChanges();
        },
        error: (err) => {
          console.error('Error agregando xuxemon', err);
          if (err.error?.error) {
            this.mensajeError = err.error.error;
          } else {
            this.mensajeError = 'Error al agregar Xuxemon';
          }
          this.mensajeExito = '';
          this.cdr.detectChanges();
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

  // ── INVENTARI ──────────────────────────────────────────────────────────────

  // Carga la lista de Xuxes disponibles para agregar al inventario
  cargarXuxesDisponibles(): void {
    this.http.get<{ xuxemons: any[]; xuxes: XuxeItem[] }>(`${this.apiUrl}/inventario/items`).subscribe({
      next: (res) => {
        this.xuxesDisponibles = res.xuxes;
        res.xuxes.forEach(x => { this.xuxesQuantitats[x.id] = 1; });
        this.cdr.detectChanges();
      },
      error: (err) => console.error('Error carregant Xuxes disponibles', err),
    });
  }

  // Abre el modal de inventario para un usuario específico
  obrirModalInventari(userId: number): void {
    this.usuarioInventariId = userId;
    this.modalInventariAbierto = true;
    this.xuxesDisponibles.forEach(x => { this.xuxesQuantitats[x.id] = 1; });
    this.mensajeExito = '';
    this.mensajeError = '';
  }

  // Cierra el modal de inventario y resetea las variables relacionadas
  tancarModalInventari(): void {
    this.modalInventariAbierto = false;
    this.usuarioInventariId = null;
  }

  // Agrega una Xuxa específica al inventario del usuario
  afegirXuxa(xuxeId: number): void {
    const cantidad = this.xuxesQuantitats[xuxeId] ?? 1;
    if (!this.usuarioInventariId || !xuxeId || cantidad < 1) return;
    this.afegindoXuxes = true;

    this.http.post<{ mensaje: string; descartado: number; slots_utilizados: number; max_slots: number }>(
      `${this.apiUrl}/inventario`,
      { user_id: this.usuarioInventariId, xuxe_id: xuxeId, cantidad }
    ).pipe(finalize(() => { this.afegindoXuxes = false; this.cdr.detectChanges(); }))
      .subscribe({
        next: (res) => {
          if (res.descartado > 0) {
            this.mensajeError = res.mensaje;
            this.mensajeExito = '';
          } else {
            this.mensajeExito = res.mensaje;
            this.mensajeError = '';
          }
          this.xuxesQuantitats[xuxeId] = 1;
          this.cdr.detectChanges();
        },
        error: (err) => {
          this.mensajeError = err.error?.message ?? 'Error afegint Xuxes';
          this.mensajeExito = '';
          this.cdr.detectChanges();
        }
      });
  }

  // ── CONFIGURACIÓ GLOBAL ────────────────────────────────────────────────────

  cargarConfig(): void {
    this.adminConfigService.getConfig().subscribe({
      next: (items) => {
        this.configItems = items;
        items.forEach(item => {
          this.configEdits[item.clave] = +item.valor;
        });
        this.cdr.detectChanges();
      },
      error: (err) => console.error('Error carregant config', err),
    });
  }

  guardarConfig(clave: string): void {
    const valor = this.configEdits[clave];
    if (valor === undefined || valor === null) return;
    this.guardantConfig[clave] = true;
    this.configMissatgeExito = '';
    this.configMissatgeError = '';

    this.adminConfigService.updateConfig(clave, valor).pipe(
      finalize(() => { this.guardantConfig[clave] = false; this.cdr.detectChanges(); })
    ).subscribe({
      next: (res) => {
        this.configMissatgeExito = res.message;
        const item = this.configItems.find(c => c.clave === clave);
        if (item) item.valor = String(valor);
        this.cdr.detectChanges();
      },
      error: (err) => {
        this.configMissatgeError = err.error?.message ?? err.message ?? 'Error desant la configuració';
        this.cdr.detectChanges();
      },
    });
  }

  getConfigLabel(clave: string): string {
    const labels: Record<string, string> = {
      xuxes_hora_recompensa: 'Hora recompensa Xuxes (0–23)',
      xuxes_quantitat_diaria: 'Quantitat diària de Xuxes',
      xuxemon_hora_recompensa: 'Hora recompensa Xuxemon (0–23)',
      infeccio_bajon: '% Bajón de Azúcar',
      infeccio_sobredosis: '% Sobredosis',
      infeccio_atracon: '% Atracón',
    };
    return labels[clave] ?? clave;
  }

  getConfigMax(clave: string): number {
    if (clave.startsWith('infeccio_')) return 100;
    if (clave.includes('hora')) return 23;
    return 999;
  }

  // ── XUXEMONS NIVELL ────────────────────────────────────────────────────────

  cargarXuxemonsNivell(): void {
    this.adminConfigService.getXuxemonsNivell().subscribe({
      next: (xuxemons) => {
        this.xuxemonsNivell = xuxemons;
        xuxemons.forEach(x => {
          this.xuxesPerPujarEdits[x.id] = x.xuxes_per_pujar;
        });
        this.cdr.detectChanges();
      },
      error: (err) => console.error('Error carregant xuxemons nivell', err),
    });
  }

  guardarXuxesPerPujar(xuxemon: XuxemonNivell): void {
    const valor = this.xuxesPerPujarEdits[xuxemon.id];
    if (!valor || valor < 1) return;
    this.guardantNivell[xuxemon.id] = true;
    this.nivellMissatgeExito = '';
    this.nivellMissatgeError = '';

    this.adminConfigService.updateXuxesPerPujar(xuxemon.id, valor).pipe(
      finalize(() => { this.guardantNivell[xuxemon.id] = false; this.cdr.detectChanges(); })
    ).subscribe({
      next: (res) => {
        this.nivellMissatgeExito = res.message;
        xuxemon.xuxes_per_pujar = res.xuxes_per_pujar;
        this.cdr.detectChanges();
      },
      error: (err) => {
        this.nivellMissatgeError = err.error?.message ?? 'Error desant';
        this.cdr.detectChanges();
      },
    });
  }

  get xuxemonsNivellPetit(): XuxemonNivell[] {
    return this.xuxemonsNivell.filter(x => x.tamano === 'Petit');
  }

  get xuxemonsNivellMitja(): XuxemonNivell[] {
    return this.xuxemonsNivell.filter(x => x.tamano === 'Mitja');
  }

  // Canviar la pestanya activa del menú
  canviarPestanya(pestanya: string): void {
    this.pestanyaActiva = pestanya;
    // Netejar missatges globals
    this.mensajeExito = '';
    this.mensajeError = '';
    this.configMissatgeExito = '';
    this.configMissatgeError = '';
    this.nivellMissatgeExito = '';
    this.nivellMissatgeError = '';
  }
}
