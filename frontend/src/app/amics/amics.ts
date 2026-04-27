import { Component, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { debounceTime, distinctUntilChanged, filter, merge, switchMap, Subscription, Subject } from 'rxjs';
import { AmicsService, Amic, PeticioAmistat } from '../services/amics.service';

@Component({
  selector: 'app-amics',
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './amics.html',
  styleUrl: './amics.css',
})

// Componente para gestionar la lista de amigos, búsqueda de usuarios y peticiones de amistad.
export class Amics implements OnDestroy {
  // Campo de texto reactivo para el buscador de usuarios
  cercaBusqueda = new FormControl('');
  // Lista de resultados devueltos por la búsqueda
  resultatsCerca: Amic[] = [];
  // Indica si hay una búsqueda en curso (para mostrar spinner, etc.)
  cercant = false;

  // Lista completa de amigos del usuario
  amics: Amic[] = [];
  // Lista de amigos que se muestran en pantalla (puede diferir durante animaciones de eliminación)
  amicsVisibles: Amic[] = [];
  // Peticiones de amistad pendientes recibidas
  peticionsRebudes: PeticioAmistat[] = [];

  // Mensajes de feedback para el usuario (éxito o error)
  missatgeExit = '';
  missatgeError = '';

  // ID del amigo cuya eliminación está pendiente de confirmar
  confirmantEliminar: number | null = null;
  // ID del amigo recién añadido (para animar su entrada)
  amicNouId: number | null = null;
  // ID del amigo que está siendo eliminado en este momento
  amicEliminantId: number | null = null;

  // ID del amigo que debe animarse al entrar, guardado mientras se espera que llegue de la API
  private amicPendentAnimacioId: number | null = null;
  // Almacén de suscripcions per poder netejar-les en destruir el component
  private subs: Subscription[] = [];
  // Subject per disparar la cerca manualment (botó o Enter) sense duplicar la petició del stream reactiu
  private cercaManual$ = new Subject<string>();
  // Timers para controlar la duración de las animaciones de entrada y salida
  private timeoutAnimacioEntrada: ReturnType<typeof setTimeout> | null = null;
  private timeoutAnimacioSortida: ReturnType<typeof setTimeout> | null = null;

  // Inyectamos el servicio de amigos, el router para navegación y el ChangeDetectorRef para actualizar la vista.
  constructor(
    private amicsService: AmicsService,
    private router: Router,
    private cdr: ChangeDetectorRef,
  ) {
    // Al iniciar el componente pedimos los datos al servicio
    this.amicsService.carregarAmics();
    this.amicsService.carregarPeticionsRebudes();

    this.subs.push(
      // Nos suscribimos a la lista de amigos del servicio para mantenerla actualizada
      this.amicsService.amics.subscribe(amics => {
        const idsAbans = new Set(this.amics.map(amic => amic.id));

        this.amics = amics;
        // Excluimos de la vista al amigo que está en proceso de eliminación (animación de salida)
        this.amicsVisibles = amics.filter(amic => amic.id !== this.amicEliminantId);

        // Si había un amigo pendiente de animar y ya aparece en la lista, lanzamos la animación
        if (
          this.amicPendentAnimacioId !== null &&
          !idsAbans.has(this.amicPendentAnimacioId) &&
          amics.some(amic => amic.id === this.amicPendentAnimacioId)
        ) {
          this.activarAnimacioNouAmic(this.amicPendentAnimacioId);
          this.amicPendentAnimacioId = null;
        }

        this.cdr.markForCheck();
      }),
      // Nos suscribimos a las peticiones recibidas para tenerlas siempre al día
      this.amicsService.peticionsRebudes.subscribe(peticions => {
        this.peticionsRebudes = peticions;
        this.cdr.markForCheck();
      }),
    );

    this.subs.push(
      // Un sol stream: unifica les tecles (amb debounce) i els clicks manuals del botó/Enter
      merge(
        this.cercaBusqueda.valueChanges.pipe(debounceTime(300), distinctUntilChanged()),
        this.cercaManual$
      ).pipe(
        filter(q => (q ?? '').trim().length >= 3),
        switchMap(q => {
          this.cercant = true;
          this.cdr.markForCheck();
          return this.amicsService.cercarUsuaris(q!.trim());
        }),
      ).subscribe({
        next: resultats => {
          this.resultatsCerca = resultats;
          this.cercant = false;
          this.cdr.markForCheck();
        },
        error: () => {
          this.cercant = false;
          this.cdr.markForCheck();
        },
      })
    );

    this.subs.push(
      // Si el text buscat té menys de 3 caràcters, netejem els resultats
      this.cercaBusqueda.valueChanges.pipe(
        filter(q => (q ?? '').trim().length < 3),
      ).subscribe(() => {
        this.resultatsCerca = [];
        this.cdr.markForCheck();
      })
    );
  }

  // Limpieza al destruir el componente: cancelamos suscripciones y timers pendientes
  ngOnDestroy(): void {
    this.subs.forEach(sub => sub.unsubscribe());
    this.cercaManual$.complete();
    if (this.timeoutAnimacioEntrada) clearTimeout(this.timeoutAnimacioEntrada);
    if (this.timeoutAnimacioSortida) clearTimeout(this.timeoutAnimacioSortida);
  }

  // Comprueba si un usuario ya está en la lista de amigos
  esAmic(id: number): boolean {
    return this.amics.some(amic => amic.id === id);
  }

  // Comprueba si hay una petición de amistad pendiente por parte de ese usuario
  peticioPendent(id: number): boolean {
    return this.peticionsRebudes.some(peticio => peticio.remitente.id === id);
  }

  // Envía una solicitud de amistad al usuario seleccionado
  enviarPeticio(destinatari: Amic): void {
    this.amicsService.enviarPeticio(destinatari.id).subscribe({
      next: () => {
        this.mostrarExit(`Solicitud enviada a ${destinatari.nombre}!`);
        this.amicsService.carregarPeticionsRebudes();
      },
      error: err => {
        this.mostrarError(err.error?.errors?.destinatarioId?.[0] ?? err.error?.message ?? 'Error al enviar la solicitud.');
      },
    });
  }

  // Acepta una petición de amistad recibida y guarda el ID para animarlo al entrar en la lista
  acceptarPeticio(peticio: PeticioAmistat): void {
    this.amicPendentAnimacioId = peticio.remitente.id;

    this.amicsService.acceptarPeticio(peticio.id).subscribe({
      next: () => this.mostrarExit(`${peticio.remitente.nombre} ahora es amigo tuyo!`),
      error: () => {
        this.amicPendentAnimacioId = null;
        this.mostrarError('Error al aceptar la solicitud.');
      },
    });
  }

  // Rechaza una petición de amistad recibida
  rebutjarPeticio(peticio: PeticioAmistat): void {
    this.amicsService.rebutjarPeticio(peticio.id).subscribe({
      next: () => this.mostrarExit('Solicitud rechazada.'),
      error: () => this.mostrarError('Error al rechazar la solicitud.'),
    });
  }

  // Marca el amigo como pendiente de confirmación antes de eliminarlo
  confirmarEliminar(id: number): void {
    this.confirmantEliminar = id;
  }

  // Cancela la confirmación de eliminación
  cancelarEliminar(): void {
    this.confirmantEliminar = null;
  }

  // Elimina un amigo con animación de salida: primero lo marca, espera ~420ms para la animación CSS y luego llama a la API
  eliminarAmic(amic: Amic): void {
    if (this.amicEliminantId !== null) return; // Evita eliminaciones simultáneas

    this.confirmantEliminar = null;
    this.amicEliminantId = amic.id;
    this.cdr.markForCheck();

    if (this.timeoutAnimacioSortida) clearTimeout(this.timeoutAnimacioSortida);
    this.timeoutAnimacioSortida = setTimeout(() => {
      // Quitamos el amigo de la vista y llamamos a la API
      this.amicsVisibles = this.amicsVisibles.filter(item => item.id !== amic.id);
      this.cdr.markForCheck();

      this.amicsService.eliminarAmic(amic.id).subscribe({
        next: () => {
          this.amicEliminantId = null;
          this.mostrarExit(`${amic.nombre} eliminado de la lista de amigos.`);
        },
        error: () => {
          this.amicEliminantId = null;
          this.amicsVisibles = [...this.amics];
          this.mostrarError('Error al eliminar el amigo.');
          this.cdr.markForCheck();
        },
      });
    }, 420);
  }

  // Devuelve la ruta de la imagen del avatar; si no tiene, usa el avatar por defecto
  getAvatarSrc(avatar: string | null): string {
    if (!avatar || avatar === 'avatarpordefecto.webp') return '/avatarpordefecto.webp';
    return '/Imatges/Xuxemons/' + avatar;
  }

  // Cerca manual: emet al Subject compartit amb el stream reactiu (no fa cap crida HTTP addicional)
  cercar(): void {
    const q = (this.cercaBusqueda.value ?? '').trim();
    if (q.length >= 3) {
      this.cercaManual$.next(q);
    }
  }

  // Navega de vuelta al menú principal
  sortir(): void {
    this.router.navigate(['/menu-principal']);
  }

  // Indica si este amigo es el recién añadido (para aplicar la animación de entrada)
  esAmicNou(id: number): boolean {
    return this.amicNouId === id;
  }

  // Indica si este amigo está siendo eliminado (para aplicar la animación de salida)
  estaEliminantAmic(id: number): boolean {
    return this.amicEliminantId === id;
  }

  // Muestra un mensaje de éxito durante 3,5 segundos y luego lo oculta
  private mostrarExit(msg: string): void {
    this.missatgeExit = msg;
    this.missatgeError = '';
    this.cdr.markForCheck();
    setTimeout(() => {
      this.missatgeExit = '';
      this.cdr.markForCheck();
    }, 3500);
  }

  // Muestra un mensaje de error durante 3,5 segundos y luego lo oculta
  private mostrarError(msg: string): void {
    this.missatgeError = msg;
    this.missatgeExit = '';
    this.cdr.markForCheck();
    setTimeout(() => {
      this.missatgeError = '';
      this.cdr.markForCheck();
    }, 3500);
  }

  // Marca un amigo como nuevo durante 1,8 segundos para que la plantilla pueda aplicarle la animación de entrada
  private activarAnimacioNouAmic(id: number): void {
    this.amicNouId = id;

    if (this.timeoutAnimacioEntrada) clearTimeout(this.timeoutAnimacioEntrada);
    this.timeoutAnimacioEntrada = setTimeout(() => {
      this.amicNouId = null;
      this.cdr.markForCheck();
    }, 1800);
  }
}