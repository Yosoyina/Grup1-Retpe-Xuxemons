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
    'avatarpordefecto.png',
    'Aigua-Petit-Bulleta.png',
    'Aigua-Petit-Esquitx.png',
    'Aigua-Petit-Goteta.png',
    'Aigua-Petit-Gotim.png',
    'Aigua-Petit-Perleta.png',
    'Aigua-Petit-Regalim.png',
    'Terra-Petit-Fanguet.png',
    'Terra-Petit-Graveta.png',
    'Terra-Petit-Grumoll.png',
    'Terra-Petit-Pedrot.png',
    'Terra-Petit-Sorreta.png',
    'Terra-Petit-Terros.png',
    'Vent-Petit-Airos.png',
    'Vent-Petit-Alenat.png',
    'Vent-Petit-Briseta.png',
    'Vent-Petit-Bufet.png',
    'Vent-Petit-Sospir.png',
    'Vent-Petit-Xiulet.png',
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
    // carreguem les dades de l'usuari al crear el component
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
        this.router.navigate(['/login']);
      }
    });
  }

  // retorna la URL correcta per a cada avatar
  getAvatarSrc(avatar: string): string {
    if (!avatar || avatar === 'avatarpordefecto.png') {
      return '/avatarpordefecto.png';
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

      setTimeout(() => {
        this.copiado = false;
      }, 1200);
    });
  }
} 