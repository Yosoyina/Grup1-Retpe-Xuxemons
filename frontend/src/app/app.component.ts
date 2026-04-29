import { DOCUMENT } from '@angular/common';
import { Component, Inject, OnInit } from '@angular/core';
import { ActivatedRoute, NavigationEnd, Router, RouterOutlet } from '@angular/router';
import { Meta, Title } from '@angular/platform-browser';
import { filter } from 'rxjs';
import { AuthService } from './services/auth.service';

/**
 * Component arrel de l'aplicació.
 *
 * Gestiona l'auto-login en arrencar, actualitza les etiquetes SEO
 * (title, description, keywords, canonical) en cada canvi de ruta
 * i escolta els events de navegació del Router.
 */
@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App implements OnInit {
  private readonly defaultTitle = 'Xuxemons';
  private readonly defaultDescription =
    'Xuxemons es una aplicacion web donde puedes gestionar tu coleccion de Xuxemons, inventario, amistades y perfil de usuario.';
  private readonly defaultKeywords =
    'Xuxemons, Xuxedex, inventario, amistades, perfil, juego web';
  private readonly defaultRobots = 'noindex, follow';
 
  constructor(
    private authService: AuthService,
    private router: Router,
    private route: ActivatedRoute,
    private titleService: Title,
    private metaService: Meta,
    @Inject(DOCUMENT) private document: Document
  ) {}
 
  // ── CICLE DE VIDA ─────────────────────────────────────────────────────────

  // En arrencar l'app, intenta restaurar la sessió validant el token al backend.
  // Si el token ha caducat o és invàlid, el neteja i l'authInterceptor
  // redirigirà al login quan el guard rebutgi la ruta.
  ngOnInit(): void {
    this.authService.intentarAutoLogin().subscribe();
    this.updateSeoTags();

    this.router.events
      .pipe(filter((event) => event instanceof NavigationEnd))
      .subscribe(() => this.updateSeoTags());
  }

  // ── SEO ─────────────────────────────────────────────────────────────────

  // Actualitza el títol, la descripció, les paraules clau, el robots i l'URL canònica
  // a partir de les dades de la ruta activa més profunda.
  private updateSeoTags(): void {
    const activeRoute = this.getDeepestActiveRoute();
    const routeTitle = activeRoute.snapshot.title;
    const routeDescription = activeRoute.snapshot.data['description'] as string | undefined;
    const routeKeywords = activeRoute.snapshot.data['keywords'] as string | undefined;
    const routeRobots = activeRoute.snapshot.data['robots'] as string | undefined;

    this.titleService.setTitle(routeTitle ?? this.defaultTitle);
    this.metaService.updateTag({
      name: 'description',
      content: routeDescription ?? this.defaultDescription
    });
    this.metaService.updateTag({
      name: 'keywords',
      content: routeKeywords ?? this.defaultKeywords
    });
    this.metaService.updateTag({
      name: 'robots',
      content: routeRobots ?? this.defaultRobots
    });

    const canonicalUrl = `${this.document.location.origin}${this.normalizeCanonicalPath(this.router.url)}`;
    let canonicalLink = this.document.querySelector('link[rel="canonical"]') as HTMLLinkElement | null;

    if (!canonicalLink) {
      canonicalLink = this.document.createElement('link');
      canonicalLink.setAttribute('rel', 'canonical');
      this.document.head.appendChild(canonicalLink);
    }

    canonicalLink.setAttribute('href', canonicalUrl);
  }

  private normalizeCanonicalPath(url: string): string {
    const pathWithoutParams = url.split('?')[0].split('#')[0] || '/';

    if (pathWithoutParams === '/') {
      return '/login';
    }

    return pathWithoutParams.endsWith('/') ? pathWithoutParams.slice(0, -1) : pathWithoutParams;
  }

  private getDeepestActiveRoute(): ActivatedRoute {
    let currentRoute = this.route;

    while (currentRoute.firstChild) {
      currentRoute = currentRoute.firstChild;
    }

    return currentRoute;
  }
}
