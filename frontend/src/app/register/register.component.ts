import { Component } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators, AbstractControl, ValidationErrors } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule],
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})
export class RegisterComponent {

  errorMessage = '';
  successMessage = '';
  isLoading = false;

  registerForm = new FormGroup({
    nombre: new FormControl('', [Validators.required, Validators.maxLength(25)]),
    apellidos: new FormControl('', [Validators.required, Validators.maxLength(25)]),
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required, Validators.minLength(6)]),
    password_confirmation: new FormControl('', Validators.required)

  }, { validators: this.contrasenjesIgualsValidator });

  constructor(private authService: AuthService) { }

  contrasenjesIgualsValidator(form: AbstractControl): ValidationErrors | null {
    const password = form.get('password')?.value;
    const confirm = form.get('password_confirmation')?.value;
    return password === confirm ? null : { contrasenjesDiferents: true };
  }

  onSubmit() {
    if (this.registerForm.invalid) {
      this.registerForm.markAllAsTouched();
      return;
    }

    this.authService.register(this.registerForm.value as any).subscribe({
      next: (response) => {
        this.successMessage = `Registre correcte! El teu ID de jugador es: ${response.user.id_jugador}`;
      },
      error: (err: HttpErrorResponse) => {
        this.errorMessage = err.error?.message ?? 'Error inesperat. Torna-ho a intentar.';
      }
    });
  }

  isFieldInvalid(field: string): boolean {
    const control = this.registerForm.get(field);
    return !!(control && control.invalid && control.touched);
  }

  getErrorMessage(field: string): string {
    const control = this.registerForm.get(field);
    if (!control || !control.errors || !control.touched) return '';

    if (control.errors['required']) return 'Aquest camp es obligatori';
    if (control.errors['email']) return 'El format del correu no es valid';
    if (control.errors['minlength']) return `Minim ${control.errors['minlength'].requiredLength} caracters`;
    if (control.errors['maxlength']) return `Maxim ${control.errors['maxlength'].requiredLength} caracters`;

    return 'Error de validacio';
  }
}