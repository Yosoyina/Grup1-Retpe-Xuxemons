import { Routes } from '@angular/router';
import { RegisterComponent } from './register/register.component';
import { LoginComponent } from './login/login';
import { MenuPrincipal } from './menu-principal/menu-principal';
import { InfoUsuari } from './info-usuari/info-usuari';
import { authGuard } from './guards/auth-guard';

export const routes: Routes = [
  {
    path: '',
    redirectTo: 'login',
    pathMatch: 'full'
  },

  {
    path: 'registrar',
    title: 'Registrar',
    component: RegisterComponent
  },

  {
    path: 'login',
    title: 'Login',
    component: LoginComponent
  },

  {
    path: 'menu-principal',
    title: 'Menu Principal',
    component: MenuPrincipal,
    canActivate: [authGuard]
  },

  {
    path: 'info-usuari',
    title: 'Info Usuari',
    component: InfoUsuari,
    canActivate: [authGuard]
  },
];