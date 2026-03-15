import { Routes } from '@angular/router';
import { RegisterComponent } from './register/register.component';
import { LoginComponent } from './login/login';
import { MenuPrincipal } from './menu-principal/menu-principal';
import { InfoUsuari } from './info-usuari/info-usuari';
import { Inventario } from './inventario/inventario';
import { authGuard } from './guards/auth-guard';
import { noAuthGuard } from './guards/no-auth-guard';
import { Xuxedex } from './xuxedex/xuxedex';
import { Admin } from './admin/admin';
import { adminGuard } from './guards/admin-guard';

export const routes: Routes = [
  {
    path: '',
    redirectTo: 'login',
    pathMatch: 'full'
  },

  // Rutas públicas (solo accesibles si NO estás autenticado)
  {
    path: 'registrar',
    title: 'Registrar',
    component: RegisterComponent,
    canActivate: [noAuthGuard]
  },
  {
    path: 'login',
    title: 'Login',
    component: LoginComponent,
    canActivate: [noAuthGuard]
  },

  // Rutas protegidas: el authGuard se aplica una sola vez al padre
  {
    path: '',
    canActivate: [authGuard],
    children: [
      {
        path: 'menu-principal',
        title: 'Menu Principal',
        component: MenuPrincipal,
      },
      {
        path: 'xuxedex',
        title: 'Xuxedex',
        component: Xuxedex,
      },
      {
        path: 'info-usuari',
        title: 'Info Usuari',
        component: InfoUsuari,
      },
      {
        path: 'admin',
        title: 'Admin',
        component: Admin,
        canActivate: [adminGuard]
      }
    ]
  }

];