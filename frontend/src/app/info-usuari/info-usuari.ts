import { Component, ChangeDetectorRef } from '@angular/core';
import { AbstractControl, FormControl, FormGroup, ReactiveFormsModule, ValidationErrors, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-info-usuari',
  imports: [ReactiveFormsModule, CommonModule],
  templateUrl: './info-usuari.html',
  styleUrl: './info-usuari.css',
})
export class InfoUsuari {

  usuari: any = null;
  errorMessage = '';
  successMessage = '';
  mostrarConfirmacio = false;
  mostrarSeleccioAvatar = false;
  mostrarModalActualitzat = false;

  // Llista d'avatars disponibles
  avatars = [
    'avatarpordefecto.webp',
    'Aigua-Petit-Bulleta.webp',
    'Aigua-Petit-Esquitx.webp',
    'Aigua-Petit-Goteta.webp',
    'Aigua-Petit-Gotim.webp',
    'Aigua-Petit-Perleta.webp',
    'Aigua-Petit-Regalim.webp',
    'Terra-Petit-Fanguet.webp',
    'Terra-Petit-Graveta.webp',
    'Terra-Petit-Grumoll.webp',
    'Terra-Petit-Pedrot.webp',
    'Terra-Petit-Sorreta.webp',
    'Terra-Petit-Terros.webp',
    'Vent-Petit-Airos.webp',
    'Vent-Petit-Alenat.webp',
    'Vent-Petit-Briseta.webp',
    'Vent-Petit-Bufet.webp',
    'Vent-Petit-Sospir.webp',
    'Vent-Petit-Xiulet.webp',
  ];

  // FormGroup para editar el perfil del usuario
  editForm = new FormGroup({
    nombre: new FormControl('', [Validators.required, Validators.maxLength(25)]),
    apellidos: new FormControl('', [Validators.required, Validators.maxLength(25)]),
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.minLength(6)]),
    password_confirmation: new FormControl('', [Validators.minLength(6)]),
  }, { validators: InfoUsuari.passwordsCoincideixen });

  // Validador personalizado para comprobar que las contraseñas coinciden
  static passwordsCoincideixen(form: AbstractControl): ValidationErrors | null {
    const pw = form.get('password')?.value;
    const conf = form.get('password_confirmation')?.value;
    if (pw && conf && pw !== conf) {
      return { passwordMismatch: true };
    }
    return null;
  }

  // Inyectamos los servicios necesarios en el constructor
  constructor(private authService: AuthService, private router: Router, private cdr: ChangeDetectorRef) {

    // 1. Mostrar dades de la caché immediatament (sense esperar HTTP)
    const usuariCached = this.authService.getUsuariActual();
    if (usuariCached) {
      this.usuari = usuariCached;
      this.editForm.patchValue({
        nombre: this.usuari.nombre,
        apellidos: this.usuari.apellidos,
        email: this.usuari.email,
      });
    }

    // 2. Refresca les dades del servidor en segon pla
    this.authService.getPerfil().subscribe({
      next: (response: any) => {
        this.usuari = response.user;
        this.editForm.patchValue({
          nombre: this.usuari.nombre,
          apellidos: this.usuari.apellidos,
          email: this.usuari.email,
        });
        this.cdr.detectChanges();
      },
      error: () => {
        if (!this.usuari) {
          this.router.navigate(['/login']);
        }
      }
    });
  }

  // retorna la URL correcta per a cada avatar
  getAvatarSrc(avatar: string | null): string {
    if (!avatar || avatar.startsWith('avatarpordefecto')) {
      return '/avatarpordefecto.webp';
    }
    return '/Imatges/Xuxemons/' + avatar;
  }

  // canvia l'avatar de l'usuari
  seleccionarAvatar(nomAvatar: string) {
    this.authService.updatePerfil({ avatar: nomAvatar }).subscribe({
      next: (response: any) => {
        this.usuari = response.user;
        this.mostrarSeleccioAvatar = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.errorMessage = 'Error en canviar l\'avatar.';
        this.cdr.detectChanges();
      }
    });
  }

  // Funció per a guardar els canvis del perfil
  guardarCanvis() {
    if (this.editForm.invalid) {
      this.editForm.markAllAsTouched();
      return;
    }

    // si el password esta buit no l'enviem
    const dades: any = {
      nombre: this.editForm.value.nombre,
      apellidos: this.editForm.value.apellidos,
      email: this.editForm.value.email,
    };
    if (this.editForm.value.password) {
      dades.password = this.editForm.value.password;
      dades.password_confirmation = this.editForm.value.password_confirmation;
    }

    this.authService.updatePerfil(dades).subscribe({
      next: (response: any) => {
        this.usuari = response.user;
        this.mostrarModalActualitzat = true;
        this.errorMessage = '';
        this.cdr.detectChanges();
      },
      error: (err: any) => {
        if (err.error?.errors) {
          this.errorMessage = Object.values(err.error.errors).flat()[0] as string;
        } else if (err.error?.message) {
          this.errorMessage = err.error.message;
        } else {
          this.errorMessage = 'Error en actualitzar el perfil.';
        }
        this.cdr.detectChanges();
      }
    });
  }

  // Funció per a mostrar el modal de confirmació d'eliminació
  confirmarEliminar() {
    this.authService.eliminarCompte().subscribe({
      next: () => {
        localStorage.removeItem('token');
        this.router.navigate(['/login']);
      },
      error: () => {
        this.errorMessage = 'Error en eliminar el compte.';
        this.mostrarConfirmacio = false;
        this.cdr.detectChanges();
      }
    });
  }

  // Funció per a tancar el modal de confirmació
  tornarAlMenu() {
    this.router.navigate(['/menu-principal']);
  }

  copiado = false;

  copiarId(texto: string) {
    if (!texto) return;

    navigator.clipboard.writeText(texto).then(() => {
      this.copiado = true;
      this.cdr.detectChanges(); // Força a Angular a mostrar el tick al moment

      setTimeout(() => {
        this.copiado = false;
        this.cdr.detectChanges(); // Torna a posar l'icona normal
      }, 2000); // 2 segons perquè doni temps a veure'l (abans estava a 5 ms)
    });
  }
} 