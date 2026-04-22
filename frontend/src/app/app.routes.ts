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
import { Amics } from './amics/amics';

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
    data: {
      description: 'Crea tu cuenta de Xuxemons para empezar a coleccionar Xuxemons, gestionar tu inventario y disfrutar del juego.',
      keywords: 'registro, crear cuenta, Xuxemons, juego web, coleccion',
      robots: 'noindex, follow'
    },
    component: RegisterComponent,
    canActivate: [noAuthGuard]
  },
  {
    path: 'login',
    title: 'Login',
    data: {
      description: 'Accede a Xuxemons para gestionar tu coleccion, inventario, amistades y perfil de usuario.',
      keywords: 'login, acceso, Xuxemons, inventario, perfil, amistades',
      robots: 'noindex, follow'
    },
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
        data: {
          description: 'Explora el menu principal de Xuxemons y accede rapidamente a la Xuxedex, inventario, amistades y perfil.',
          keywords: 'menu principal, Xuxemons, Xuxedex, inventario, amistades, perfil',
          robots: 'noindex, nofollow'
        },
        component: MenuPrincipal,
      },
      {
        path: 'xuxedex',
        title: 'Xuxedex',
        data: {
          description: 'Consulta la Xuxedex de Xuxemons para ver tus criaturas, sus tipos, tamano, evoluciones y estado.',
          keywords: 'Xuxedex, Xuxemons, evolucion, criaturas, coleccion',
          robots: 'noindex, nofollow'
        },
        component: Xuxedex,
      },
      {
        path: 'info-usuari',
        title: 'Info Usuari',
        data: {
          description: 'Gestiona tu perfil de usuario en Xuxemons, actualiza tus datos personales y personaliza tu avatar.',
          keywords: 'perfil, usuario, avatar, configuracion, Xuxemons',
          robots: 'noindex, nofollow'
        },
        component: InfoUsuari,
      },
      {
        path: 'inventario',
        title: 'Inventario',
        data: {
          description: 'Administra tu inventario de Xuxemons con xuxes apilables, objetos especiales y detalles de cada recurso.',
          keywords: 'inventario, xuxes, objetos, mochila, Xuxemons',
          robots: 'noindex, nofollow'
        },
        component: Inventario,
      },
      {
        path: 'amics',
        title: 'Amics',
        data: {
          description: 'Busca amigos en Xuxemons, envia solicitudes, acepta peticiones y administra tu lista de amistades.',
          keywords: 'amigos, amistades, solicitudes, social, Xuxemons',
          robots: 'noindex, nofollow'
        },
        component: Amics,
      },
      {
        path: 'batalla',
        redirectTo: 'menu-principal',
        pathMatch: 'full'
      },
      {
        path: 'xat',
        redirectTo: 'menu-principal',
        pathMatch: 'full'
      },
      {
        path: 'admin',
        title: 'Admin',
        data: {
          description: 'Panel de administracion de Xuxemons para gestionar usuarios, configuracion global y parametros del sistema.',
          keywords: 'admin, administracion, usuarios, configuracion, Xuxemons',
          robots: 'noindex, nofollow'
        },
        component: Admin,
        canActivate: [adminGuard]
      },
      {
        path: '**',
        redirectTo: 'menu-principal'
      }
    ]
  },
  {
    path: '**',
    redirectTo: 'login'
  }

];
