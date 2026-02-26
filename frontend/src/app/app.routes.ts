import { Routes } from '@angular/router';
import { RegisterComponent } from './register/register.component';
import { LoginComponent } from './login/login';
import { MenuPrincipal } from './menu-principal/menu-principal';
import { PaginaUsuario } from './pagina-usuario/pagina-usuario';

export const routes: Routes = [
  { path: 'registrar', 
    title: 'Registrar', 
    component: RegisterComponent 
  },

  { path: 'login', 
    title: 'Login'
    ,component: LoginComponent 
  },

  { path: 'menu-principal', 
    title: 'Menu Principal',
    component: MenuPrincipal, 
    children: [
      { path: 'pagina-usuario', title: 'Página de Usuario', component: PaginaUsuario },
    ]
  },
];