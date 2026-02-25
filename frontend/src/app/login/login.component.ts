import { Component } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-login',
  imports: [],
  templateUrl: './login.html',
  styleUrl: './login.css',
})
export class LoginComponent {
  errorMessage = '';
  successMessage = '';

  // FormGroup amb els camps del login
  loginForm = new FormGroup({
    id_jugador: new FormControl('', Validators.required),
    password: new FormControl('', Validators.required)
  });

  constructor(private authService: AuthService) { }

  // quan es fa submit comprovem si el form es valid
  onSubmit() {
    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      return;
    }

    this.authService.login(this.loginForm.value as any).subscribe({
      next: (response: any) => {
        this.successMessage = `Benvingut, ${response.user.nombre}! ID: ${response.user.id_jugador}`;
      },
      error: (err: HttpErrorResponse) => {
        this.errorMessage = err.error?.message ?? 'Error inesperat. Torna-ho a intentar.';
      }
    });
  }

  // comprova si un camp es invalid i l'usuari ja l'ha tocat
  isFieldInvalid(field: string): boolean {
    const control = this.loginForm.get(field);
    return !!(control && control.invalid && control.touched);
  }
}