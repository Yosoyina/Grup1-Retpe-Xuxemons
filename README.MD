# Xuxemons – Documentació Tècnica

## Índex

1. [Descripció general i objectius](README.md#1-descripció-general-i-objectius)
2. [Arquitectura de l'aplicació](README.md#2-arquitectura-de-laplicació)
3. [Diagrama de contenidors (Docker)](README.md#3-diagrama-de-contenidors-docker)
4. [Estructura de carpetes](README.md#4-estructura-de-carpetes)
5. [Tecnologies utilitzades i justificació](README.md#5-tecnologies-utilitzades-i-justificació)
6. [Requisits per al desplegament](README.md#6-requisits-per-al-desplegament)
7. [Instal·lació i posada en marxa en local](README.md#7-installació-i-posada-en-marxa-en-local)
8. [Variables d'entorn](README.md#8-variables-dentorn)
9. [API – Resum d'endpoints](README.md#9-api--resum-dendpoints)
10. [Model de dades](README.md#10-model-de-dades)

---

## 1. Descripció general i objectius

**Xuxemons** és una aplicació web de col·lecció i gestió de criatures virtuals (els *Xuxemons*). Els jugadors poden registrar-se, obtenir la seva col·lecció mitjançant recompenses diàries, alimentar i evolucionar les seves criatures, gestionar un inventari d'objectes, i afegir amics amb els quals compartir l'experiència.

### Objectius principals

- Oferir un sistema de **col·lecció progressiva** de Xuxemons amb tres tipus d'element (Aigua, Terra, Aire) i tres mides (Petit, Mitjà, Gran).
- Implementar una **Xuxedex** personal que mostra l'estat de cada Xuxemon: nivell, malalties, possibilitat d'evolució.
- Gestionar un **inventari** amb dos tipus d'objectes: apilables (Xuxes per alimentar) i no apilables (vacunes per curar).
- Incloure un sistema de **recompensa diària** per fomentar l'accés diari.
- Disposar d'un **panell d'administració** per gestionar usuaris, configuració global i paràmetres de cada criatura.
- Permetre la **cerca i gestió d'amistats** entre jugadors.

---

## 2. Arquitectura de l'aplicació

L'aplicació segueix una arquitectura de **tres capes desacoblades** que es comuniquen via API REST:

```
┌──────────────────────────────────────────────────────────────┐
│                        Navegador                             │
│            Angular 21 (SPA – port 4200)                      │
└─────────────────────────┬────────────────────────────────────┘
                          │  HTTP/JSON (JWT Bearer Token)
┌─────────────────────────▼────────────────────────────────────┐
│               Laravel 12 API (port 8000)                     │
│   Auth · Xuxemons · Inventari · Amics · Admin · Rewards      │
└─────────────────────────┬────────────────────────────────────┘
                          │  PDO / Eloquent ORM
┌─────────────────────────▼────────────────────────────────────┐
│                   MySQL 8.0 (port 3306)                      │
│                    Base de dades relacional                   │
└──────────────────────────────────────────────────────────────┘
```

### Frontend (Angular SPA)

- Aplicació **Single Page Application** que s'executa completament al navegador.
- Comunicació amb el backend exclusivament via `HttpClient` amb un interceptor JWT que afegeix automàticament el token a totes les peticions.
- Gestió de rutes protegides amb guards (`authGuard`, `noAuthGuard`, `adminGuard`).
- Estat reactiu amb `BehaviorSubject` als serveis, sense necessitat de llibreria d'estat externa.

### Backend (Laravel API)

- API REST **stateless** basada en Laravel 12.
- Autenticació mitjançant **JWT** (`tymon/jwt-auth`): el token es genera en el login i s'adjunta a cada petició protegida.
- Middleware `admin` per protegir les rutes del panell d'administració.
- Cada recurs té el seu propi controlador i model Eloquent.

### Base de dades (MySQL)

- Base de dades relacional **MySQL 8.0**.
- Esquema gestionat amb **migracions** de Laravel per garantir reproductibilitat.
- Dades inicials carregades amb **seeders** (usuaris, Xuxemons, inventari, vacunes).

---

## 3. Diagrama de contenidors (Docker)

L'entorn de desenvolupament complet s'aixeca amb un únic `docker-compose up`.

```
┌─────────────────────────────────────────────────────────────────┐
│                    Docker network: red (bridge)                 │
│                                                                 │
│  ┌─────────────────────┐    ┌──────────────────────────────┐   │
│  │  xuxemon-frontend   │    │      xuxemon-backend         │   │
│  │  node:20-alpine     │    │  php:8.3-apache (Dockerfile) │   │
│  │  port 4200:4200     │───▶│  port 8000:80                │   │
│  │  npm start          │    │  Apache + PHP-FPM + Composer  │   │
│  └─────────────────────┘    └──────────────┬───────────────┘   │
│                                            │                   │
│                              ┌─────────────▼───────────────┐   │
│                              │         mysql-db             │   │
│                              │       mysql:8.0              │   │
│                              │       port 3306:3306         │   │
│                              │  volume: mysql_data          │   │
│                              └─────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### Serveis Docker

| Servei | Imatge | Port | Descripció |
|---|---|---|---|
| `xuxemon-frontend` | `node:20-alpine` | `4200` | Servidor de desenvolupament Angular |
| `xuxemon-backend` | `php:8.3-apache` (custom) | `8000` | API Laravel sobre Apache |
| `mysql-db` | `mysql:8.0` | `3306` | Base de dades relacional |

### Volums

| Volum | Descripció |
|---|---|
| `mysql_data` | Persisteix les dades de MySQL entre reinicis del contenidor |
| `./frontend:/app` | Muntatge en calent del codi font del frontend |
| `./backend:/var/www/html` | Muntatge en calent del codi font del backend |

---

## 4. Estructura de carpetes

```
Grup1-Retpe-Xuxemons/
├── docker-compose.yml
├── README.md
│
├── frontend/                         # Angular 21 SPA
│   ├── angular.json
│   ├── package.json
│   ├── tsconfig.json
│   ├── public/
│   │   └── Imatges/                  # Imatges estàtiques (avatars, Xuxemons)
│   └── src/
│       ├── index.html                # Punt d'entrada HTML (càrrega de fonts)
│       ├── main.ts
│       ├── styles.css
│       └── app/
│           ├── app.component.ts      # Component arrel (SEO, auto-login)
│           ├── app.config.ts         # Configuració global Angular
│           ├── app.routes.ts         # Definició de rutes
│           ├── admin/                # Panell d'administració
│           ├── amics/                # Gestió d'amistats
│           ├── info-usuari/          # Perfil d'usuari
│           ├── inventario/           # Inventari del jugador
│           ├── login/                # Formulari de login
│           ├── menu-principal/       # Menú principal + recompensa diària
│           ├── register/             # Formulari de registre
│           ├── xuxedex/              # Col·lecció de Xuxemons
│           ├── shared/
│           │   └── confirm-dialog/   # Component reutilitzable de confirmació
│           ├── services/             # Serveis Angular (lògica HTTP)
│           │   ├── auth.service.ts
│           │   ├── amics.service.ts
│           │   ├── inventario.service.ts
│           │   ├── xuxemon.service.ts
│           │   ├── reward.service.ts
│           │   ├── admin.service.ts
│           │   └── admin-config.service.ts
│           ├── guards/               # Guards de rutes
│           │   ├── auth-guard.ts
│           │   ├── no-auth-guard.ts
│           │   └── admin-guard.ts
│           ├── interceptors/
│           │   └── auth.interceptor.ts  # Afegeix JWT a cada petició HTTP
│           └── config/
│               └── api.config.ts     # URL base de la API
│
└── backend/                          # Laravel 12 API
    ├── Dockerfile
    ├── composer.json
    ├── artisan
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/          # Un controlador per recurs
    │   │   │   ├── AuthController.php
    │   │   │   ├── UserController.php
    │   │   │   ├── XuxemonsController.php
    │   │   │   ├── InventarioController.php
    │   │   │   ├── VacunesController.php
    │   │   │   ├── RewardController.php
    │   │   │   ├── AmigosController.php
    │   │   │   ├── PeticionesAmistadController.php
    │   │   │   └── AdminConfigController.php
    │   │   └── Middleware/
    │   ├── Models/                   # Models Eloquent
    │   │   ├── User.php
    │   │   ├── Xuxemons.php
    │   │   ├── Xuxedex.php
    │   │   ├── Xuxes.php
    │   │   ├── Inventario.php
    │   │   ├── Amigos.php
    │   │   ├── Peticiones_amistad.php
    │   │   ├── DailyReward.php
    │   │   └── SystemConfig.php
    │   └── Services/                 # Lògica de negoci separada dels controladors
    │       ├── DailyRewardService.php
    │       ├── InventarioService.php
    │       └── XuxedexService.php
    ├── database/
    │   ├── migrations/               # Historial de l'esquema de BD
    │   └── seeders/                  # Dades inicials
    │       ├── DatabaseSeeder.php
    │       ├── UserSeeder.php
    │       ├── XuxemonsSeeder.php
    │       ├── InventarioSeeder.php
    │       └── VacunesSeeder.php
    ├── routes/
    │   └── api.php                   # Definició de tots els endpoints REST
    └── public/
        └── Imatges/                  # Imatges de Xuxemons servides per Apache
```

---

## 5. Tecnologies utilitzades i justificació

### Frontend

| Tecnologia | Versió | Justificació |
|---|---|---|
| **Angular** | 21 | Framework SPA robust amb sistema de mòduls, DI, routing i formularis reactius integrats. Ideal per a aplicacions amb estat complex. |
| **TypeScript** | 5.x | Tipat estàtic que redueix errors en temps de compilació i millora la mantenibilitat. |
| **RxJS** | 7.8 | Gestió de fluxos asíncrons (HTTP, events) amb operadors com `switchMap`, `debounceTime`, `BehaviorSubject`. |
| **Angular Router** | 21 | Navegació declarativa amb guards per protegir rutes privades i d'administrador. |
| **HTML5 / CSS3** | – | Maquetació semàntica i estils personalitzats per component sense dependència de frameworks CSS externs. |

### Backend

| Tecnologia | Versió | Justificació |
|---|---|---|
| **PHP** | 8.3 | Llenguatge madur per a aplicacions web, requerit per Laravel. |
| **Laravel** | 12 | Framework MVC amb ORM (Eloquent), migracions, sistema de middleware i enrutament expressiu. |
| **JWT Auth** (`tymon/jwt-auth`) | 2.2 | Autenticació stateless via tokens, adequada per a APIs consumides per SPAs. |
| **MySQL** | 8.0 | Base de dades relacional estable i de rendiment provat, amb bon suport d'Eloquent. |
| **Apache** | 2.4 | Servidor web inclòs a la imatge PHP oficial. `mod_rewrite` habilitat per al routing de Laravel. |
| **Composer** | latest | Gestor de dependències PHP estàndard. |

### Infraestructura

| Tecnologia | Versió | Justificació |
|---|---|---|
| **Docker** | 20+ | Contenidors que garanteixen que l'entorn és idèntic en qualsevol màquina, sense instal·lacions manuals. |
| **Docker Compose** | v2 | Orquestració dels tres serveis (frontend, backend, BD) amb un únic fitxer de configuració. |
| **Node.js** | 20 (Alpine) | Entorn d'execució per al servidor de desenvolupament d'Angular i `npm`. |

---

## 6. Requisits per al desplegament

### Requisits de maquinari (mínims)

- **CPU**: 2 nuclis
- **RAM**: 4 GB (recomanat 8 GB per córrer els tres contenidors còmodament)
- **Disc**: 5 GB lliures (imatges Docker + codi + BD)

### Requisits de programari

| Programari | Versió mínima | Notes |
|---|---|---|
| **Docker Engine** | 20.10 | [Instal·lació oficial](https://docs.docker.com/engine/install/) |
| **Docker Compose** | 2.0 (plugin) | Inclòs a Docker Desktop. En Linux: `apt install docker-compose-plugin` |
| **Git** | – | Per clonar el repositori |

> **No** cal tenir PHP, Node.js, ni MySQL instal·lats localment. Tot s'executa dins dels contenidors.

### Ports necessaris lliures

| Port | Servei |
|---|---|
| `4200` | Frontend Angular |
| `8000` | Backend Laravel / API |
| `3306` | MySQL (opcional, per a clients de BD externs) |

---

## 7. Instal·lació i posada en marxa en local

### Pas 1 – Clonar el repositori

```bash
git clone <url-del-repositori>
cd Grup1-Retpe-Xuxemons
```

### Pas 2 – Configurar les variables d'entorn del backend

```bash
cp backend/.env.example backend/.env
```

Editar `backend/.env` i ajustar els valors si cal (vegeu la secció [Variables d'entorn](README.md#8-variables-dentorn)). Per a un entorn local amb Docker els valors per defecte ja funcionen.

### Pas 3 – Aixecar l'entorn amb Docker Compose

```bash
docker compose up --build
```

Aquesta comanda:
1. Construeix la imatge del backend (`php:8.3-apache` + extensions PHP + Composer).
2. Arranca MySQL i espera que estigui sa (`healthcheck`).
3. Installa les dependències PHP (`composer install`).
4. Genera la clau JWT (`php artisan jwt:secret`).
5. Executa les migracions (`php artisan migrate`).
6. Carrega els seeders amb dades inicials (`php artisan db:seed`).
7. Inicia Apache al backend.
8. Installa les dependències Node.js i arranca el servidor de desenvolupament Angular.

> La primera arrencada pot tardar 3-5 minuts mentre es descarreguen les imatges i s'instal·len les dependències.

### Pas 4 – Accedir a l'aplicació

| URL | Descripció |
|---|---|
| `http://localhost:4200` | Aplicació Angular (frontend) |
| `http://localhost:8000/api` | API REST (backend) |

### Aturar l'entorn

```bash
docker compose down
```

Per aturar i **eliminar les dades de la base de dades** (volum):

```bash
docker compose down -v
```

### Reiniciar amb dades netes

```bash
docker compose down -v
docker compose up --build
```

---

## 8. Variables d'entorn

Les variables d'entorn del backend es defineixen al fitxer `backend/.env`. En entorn Docker, algunes d'elles es poden sobreescriure directament des de `docker-compose.yml` (la secció `environment` del servei `backend` té prioritat).

### Variables crítiques

| Variable | Valor per defecte (Docker) | Descripció |
|---|---|---|
| `APP_KEY` | *(buit – es genera automàticament)* | Clau de xifrat de Laravel. Es genera amb `php artisan key:generate`. |
| `APP_ENV` | `local` | Entorn d'execució. Canviar a `production` en desplegament real. |
| `APP_DEBUG` | `true` | Activa els missatges d'error detallats. **Desactivar en producció.** |
| `APP_URL` | `http://localhost` | URL base de l'aplicació. |
| `JWT_SECRET` | *(generat per `jwt:secret`)* | Clau secreta per signar els tokens JWT. Es genera automàticament en arrencar. |

### Variables de base de dades

| Variable | Valor per defecte (Docker) | Descripció |
|---|---|---|
| `DB_CONNECTION` | `mysql` | Driver de connexió. |
| `DB_HOST` | `basedatos` | Nom del servei Docker (actua com a hostname dins de la xarxa interna). |
| `DB_PORT` | `3306` | Port MySQL. |
| `DB_DATABASE` | `xuxemonsbd` | Nom de la base de dades. |
| `DB_USERNAME` | `dev_user` | Usuari de la base de dades. |
| `DB_PASSWORD` | `1234` | Contrasenya de la base de dades. **Canviar en producció.** |

### Variables de sessió i cache

| Variable | Valor per defecte | Descripció |
|---|---|---|
| `SESSION_DRIVER` | `database` | Driver d'emmagatzematge de sessions. |
| `SESSION_LIFETIME` | `120` | Durada de la sessió en minuts. |
| `CACHE_STORE` | `database` | Driver de cache. |

### Variables de log

| Variable | Valor per defecte | Descripció |
|---|---|---|
| `LOG_CHANNEL` | `stack` | Canal de log principal. |
| `LOG_LEVEL` | `debug` | Nivell mínim de log. Canviar a `error` en producció. |

> **Nota de seguretat:** El fitxer `.env` mai s'ha de pujar al repositori. Està inclòs al `.gitignore` del backend.

---

## 9. API – Resum d'endpoints

### Rutes públiques (sense autenticació)

| Mètode | Endpoint | Descripció |
|---|---|---|
| `POST` | `/api/register` | Registre d'un nou usuari |
| `POST` | `/api/login` | Login i obtenció del token JWT |

### Rutes protegides (requereixen `Authorization: Bearer <token>`)

| Mètode | Endpoint | Descripció |
|---|---|---|
| `POST` | `/api/logout` | Tancar sessió |
| `GET` | `/api/profile` | Obtenir perfil de l'usuari autenticat |
| `PUT` | `/api/profile` | Actualitzar perfil |
| `DELETE` | `/api/profile` | Eliminar compte |
| `POST` | `/api/reward` | Reclamar la recompensa diària |
| `GET` | `/api/users/search?q=` | Cercar usuaris per ID de jugador |
| `GET` | `/api/xuxedex` | Obtenir la Xuxedex de l'usuari autenticat |
| `GET` | `/api/xuxemons/{id}/evolucions` | Cadena d'evolucions d'un Xuxemon |
| `POST` | `/api/xuxemons/{id}/evolucionar` | Evolucionar un Xuxemon |
| `POST` | `/api/xuxemons/{id}/feed` | Alimentar un Xuxemon (pot causar malaltia) |
| `POST` | `/api/xuxemons/{id}/curar` | Curar un Xuxemon malalt |
| `GET` | `/api/inventario` | Obtenir l'inventari del jugador |
| `POST` | `/api/vacunes/aplicar` | Aplicar una vacuna a un Xuxemon |
| `POST` | `/api/amigos/peticion` | Enviar sol·licitud d'amistat |
| `POST` | `/api/amigos/peticion/{id}/aceptar` | Acceptar sol·licitud |
| `POST` | `/api/amigos/peticion/{id}/rechazar` | Rebutjar sol·licitud |
| `GET` | `/api/amigos` | Llistat d'amics |
| `GET` | `/api/amigos/peticiones-pendientes` | Sol·licituds rebudes pendents |
| `GET` | `/api/amigos/peticiones-enviadas` | Sol·licituds enviades pendents |
| `DELETE` | `/api/amigos/{friendId}` | Eliminar un amic |

### Rutes d'administrador (requereixen rol `admin`)

| Mètode | Endpoint | Descripció |
|---|---|---|
| `GET` | `/api/admin/usuarios` | Llistar tots els usuaris |
| `PUT` | `/api/admin/usuarios/{id}/toggle` | Activar / desactivar compte |
| `PUT` | `/api/admin/usuarios/{id}/toggle-role` | Canviar rol (user ↔ admin) |
| `GET` | `/api/admin/xuxedex` | Xuxedex d'un usuari específic |
| `POST` | `/api/admin/xuxedex` | Afegir Xuxemon aleatori a un usuari |
| `GET` | `/api/admin/inventario/items` | Llistar tots els items disponibles |
| `POST` | `/api/admin/inventario` | Afegir item a l'inventari d'un usuari |
| `GET` | `/api/admin/config` | Obtenir configuració global del sistema |
| `PUT` | `/api/admin/config/{clave}` | Actualitzar un paràmetre de configuració |
| `GET` | `/api/admin/xuxemons-nivell` | Xuxemons amb el seu cost d'evolució |
| `PUT` | `/api/admin/xuxemons-nivell/{id}` | Actualitzar cost d'evolució |

---

## 10. Model de dades

### Taules principals

| Taula | Descripció |
|---|---|
| `users` | Jugadors registrats. Conté `id_jugador`, `role`, `actiu`, `avatar`, timestamps de recompenses. |
| `xuxemons` | Catàleg global de totes les criatures (nom, tipus, mida, imatge, `xuxes_per_pujar`). |
| `xuxedex` | Relació usuari–Xuxemon capturat. Registra si està evolucionat, bloquejat o malalt (`enfermedad`). |
| `xuxes` | Catàleg d'objectes (Xuxes apilables i vacunes no apilables). |
| `inventario` | Items que posseeix cada jugador (FK a `users` i `xuxes`, amb quantitat). |
| `amigos` | Relació d'amistat confirmada entre dos jugadors (FK doble a `users`). |
| `peticiones_amistad` | Sol·licituds d'amistat amb estat (`pendiente`, `aceptado`, `rechazado`). |
| `system_config` | Parells clau–valor per a la configuració global editable per l'admin. |
| `daily_rewards` | Registre de recompenses diàries reclamades per cada jugador. |

### Relacions clau

```
users ──< xuxedex >── xuxemons     (molts a molts a través de xuxedex)
users ──< inventario >── xuxes     (molts a molts a través de inventario)
users ──< amigos                   (auto-relació)
users ──< peticiones_amistad       (remitent i destinatari)
users ──< daily_rewards            (una per dia màxim)
```
