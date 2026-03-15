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

  // llista de avatars disponibles
  avatars = [
    'avatarpordefecto.png',
    'Futsin.png',
    'Piturrin.png',
    'Moxilla.png',
  ];

  editForm = new FormGroup({
    nombre: new FormControl('', [Validators.required, Validators.maxLength(25)]),
    apellidos: new FormControl('', [Validators.required, Validators.maxLength(25)]),
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.minLength(6)]),
    password_confirmation: new FormControl('', [Validators.minLength(6)]),
  }, { validators: InfoUsuari.passwordsCoincideixen });

  static passwordsCoincideixen(form: AbstractControl): ValidationErrors | null {
    const pw = form.get('password')?.value;
    const conf = form.get('password_confirmation')?.value;
    if (pw && conf && pw !== conf) {
      return { passwordMismatch: true };
    }
    return null;
  }

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