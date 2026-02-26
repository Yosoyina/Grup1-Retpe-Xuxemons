import { Routes } from '@angular/router';
import { RegisterComponent } from './register/register.component';
import { LoginComponent } from './login/login';
import { MenuPrincipal } from './menu-principal/menu-principal';
import { InfoUsuari } from './info-usuario/info-usuari';

export const routes: Routes = [
  { 
    path: 'registrar', 
    title: 'Registrar', 
    component: RegisterComponent 
  },

  { 
    path: 'login', 
    title: 'Login'
    ,component: LoginComponent 
  },

  { 
    path: 'menu-principal', 
    title: 'Menu Principal',
    component: MenuPrincipal, 
    children: [
      { 
        path: 'info-usuari', 
        title: 'Página de Usuario', 
        component: InfoUsuari 
      },
    ]
  },
];