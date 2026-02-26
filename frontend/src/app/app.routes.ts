import { Routes } from '@angular/router';
import { RegisterComponent } from './register/register.component';
import { LoginComponent } from './login/login';

export const routes: Routes = [
  { path: 'registrar', component: RegisterComponent },
  { path: 'login', component: LoginComponent }
];