import { Component, ChangeDetectorRef } from '@angular/core';
import { FormControl, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';
import { AuthService } from '../services/auth.service';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';

@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule, CommonModule, RouterLink],
  templateUrl: './login.html',
  styleUrl: './login.css',
})
export class LoginComponent {
  errorMessage = '';
  isLoading = false;
  successMessage = '';

  // CLAVE: solo mostramos errores de required después de intentar enviar
  submitted = false;

  loginForm = new FormGroup({
    id_jugador: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    password: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
  });

  constructor(
    private authService: AuthService,
    private router: Router,
    private cdr: ChangeDetectorRef
  ) {}

  onSubmit() {
    if (this.isLoading) return;

    this.submitted = true;
    this.loginForm.markAllAsTouched();

    if (this.loginForm.invalid) return;

    this.isLoading = true;
    this.errorMessage = '';
    this.successMessage = '';

    this.authService.login(this.loginForm.getRawValue() as any).subscribe({
      next: () => {
        this.isLoading = false;
        this.router.navigate(['/menu-principal']);
      },
      error: (err: HttpErrorResponse) => {
        this.isLoading = false;
        this.errorMessage =
          err.error?.message ?? 'Credencials incorrectes. Torna-ho a provar.';
        this.cdr.detectChanges();
      },
    });
  }

  // ahora NO depende de touched (click), depende de submitted (login)
  isFieldInvalid(field: 'id_jugador' | 'password'): boolean {
    const control = this.loginForm.get(field);
    return !!(control && control.invalid && this.submitted);
  }
}