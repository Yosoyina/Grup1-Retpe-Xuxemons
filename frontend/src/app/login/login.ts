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

  // FormGroup amb els camps del login
  loginForm = new FormGroup({
    id_jugador: new FormControl('', Validators.required),
    password: new FormControl('', Validators.required)
  });

  constructor(private authService: AuthService, private router: Router, private cdr: ChangeDetectorRef) { }

  // quan es fa submit comprovem si el form es valid
  onSubmit() {
    if (this.loginForm.invalid || this.isLoading) return;

    this.loginForm.markAllAsTouched();
    this.isLoading = true;
    this.errorMessage = '';


    this.authService.login(this.loginForm.value as any).subscribe({
      next: () => {
        this.isLoading = false;
        this.router.navigate(['/menu-principal']);
      },
      error: (err: HttpErrorResponse) => {
        this.isLoading = false;
        this.errorMessage = err.error?.message ?? 'Credencials incorrectes. Torna-ho a provar.';
        this.cdr.detectChanges();

      }
    });
  }

  // comprova si un camp es invalid i l'usuari ja l'ha tocat
  isFieldInvalid(field: string): boolean {
    const control = this.loginForm.get(field);
    return !!(control && control.invalid && control.touched);
  }
}
