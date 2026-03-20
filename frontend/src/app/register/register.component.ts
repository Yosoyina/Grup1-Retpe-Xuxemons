import { Component } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators, AbstractControl, ValidationErrors } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { AuthService } from '../services/auth.service';
import { Router, RouterLink } from '@angular/router';
import { ChangeDetectorRef } from '@angular/core';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule, RouterLink],
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})
export class RegisterComponent {

  errorMessage = '';
  successMessage = '';
  isLoading = false;
  showModal = false;
  idJugadorGenerat = '';
  submitted = false;

  // FormGroup para el formulario de registro
  registerForm = new FormGroup({
    nombre: new FormControl('', [Validators.required, Validators.maxLength(25)]),
    apellidos: new FormControl('', [Validators.required, Validators.maxLength(25)]),
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required, Validators.minLength(6)]),
    password_confirmation: new FormControl('', Validators.required)

  }, { validators: this.contrasenyesIgualsValidator });

  // Inyectamos los servicios necesarios en el constructor
  constructor(private authService: AuthService, private router: Router, private cdr: ChangeDetectorRef) { }

  // Validador personalizado para comprobar que las contraseñas coinciden
  contrasenyesIgualsValidator(form: AbstractControl): ValidationErrors | null {
    const password = form.get('password')?.value;
    const confirm = form.get('password_confirmation')?.value;
    return password === confirm ? null : { contrasenyesDiferents: true };
  }
  
  // Función para manejar el envío del formulario de registro
  onSubmit() {
    this.submitted = true;
    this.registerForm.markAllAsTouched();
    if (this.registerForm.invalid) return;

    this.isLoading = true;
    this.errorMessage = '';

    this.authService.register(this.registerForm.value as any).subscribe({
      next: (response) => {
        this.isLoading = false;
        this.idJugadorGenerat = response.user.id_jugador;
        this.showModal = true;
        this.cdr.detectChanges();
      },
      error: (err: HttpErrorResponse) => {
        this.isLoading = false;
        this.errorMessage = err.error?.errors
          ? Object.values(err.error.errors).flat()[0] as string
          : err.error?.message ?? 'Error inesperat. Torna-ho a intentar.';
        this.cdr.detectChanges();
      }
    });
  }

  // Función para ir a la página de login
  anarAlLogin() {
    localStorage.removeItem('token');
    this.router.navigate(['/login']);
  }

  // Función para verificar si un campo del formulario es inválido y se ha intentado enviar
  isFieldInvalid(field: string): boolean {
    const control = this.registerForm.get(field);
    return !!(control && control.invalid && this.submitted);
  }

  // Función para obtener el mensaje de error correspondiente a un campo del formulario
  getErrorMessage(field: string): string {
    const control = this.registerForm.get(field);
    if (!control || !control.errors || !this.submitted) return '';

    if (control.errors['required']) return 'Aquest camp es obligatori';
    if (control.errors['email']) return 'El format del correu no es valid';
    if (control.errors['minlength']) return `Minim ${control.errors['minlength'].requiredLength} caracters`;
    if (control.errors['maxlength']) return `Maxim ${control.errors['maxlength'].requiredLength} caracters`;

    return 'Error de validacio';
  }

  copiat = false;

  copiarID() {
    navigator.clipboard.writeText(this.idJugadorGenerat);
    this.copiat = true;

    setTimeout(() => {
      this.copiat = false;
    }, 2000);
  }

}

