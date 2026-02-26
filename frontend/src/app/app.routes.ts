import { Routes } from '@angular/router';
import { RegisterComponent } from './register/register.component';
import { LoginComponent } from './login/login';
import { MenuPrincipal } from './menu-principal/menu-principal';

export const routes: Routes = [
  { path: 'registrar', component: RegisterComponent },
  { path: 'login', component: LoginComponent },
  { path: 'menu', component: MenuPrincipal },
];