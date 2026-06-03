---
source_url: https://gitlab.com/hermesintl/applications-departementales/shop-maintenance-app
fetched: 2026-06-03
source_type: gitlab-repo
---

# Shop Maintenance App (Hermes)

Internal ticketing web application for Hermes boutiques worldwide. Three-repo structure: backend (Symfony 4.4 API), frontend (Angular 17 SPA), and internationalization (translation files).

## Backend: shop-maintenance-back

- **Framework**: Symfony 4.4 with API Platform 2.5.8
- **PHP**: 7.4
- **Database**: MySQL 5.7
- **Architecture**: API-first with Doctrine ORM, JWT auth (Lexik), Nelmio CORS
- **Key packages**: AWS SDK, Firebase JWT, Guzzle, VichUploader, Eluceo iCal, Gedmo extensions
- **Dev tools**: PHP-CS-Fixer, PHPStan, Rector, PHPUnit, GrumPHP
- **Docker**: docker-compose with nginx, php-fpm, mysql, mailhog, pma
- **CI/CD**: GitLab CI with stages: pre-gates, quality-security-gates, package, security-check-image, generate-certificates, deploy, audit

### Domain Model (Entities)

- **Ticket** — core entity with unique ID format `\d{6}-\d{3}(-\d{1,2})?`, workflow status validation, media attachments (quotation, zoom-in, zoom-out), vendor assignment, date tracking (working dates, finished dates for vendor/store/HCT), urgency flag, quick ticket flag, security required flag, MEO ID
- **Store** — boutique info: code, name, short name, address, tel, email, cover image, open/close status, display order, country, store layouts
- **User** — AD login, username, name, email, tel, role, enabled status, lang, countries, stores, notification choices, vendor contacts (main/secondary/third/fourth)
- **Vendor** — maintenance vendors with contacts, stores, tickets
- **Country** — country list with stores and users
- **Area / SubArea** — store physical areas and sub-areas
- **Category / SubCategory** — ticket categorization
- **TicketStatus** — workflow statuses with ordering
- **TicketComment** — threaded comments on tickets
- **TicketChangeLog** — audit trail of ticket changes
- **TicketLayout** — store layout associations
- **MediaObject** — file uploads via VichUploader
- **Role** — RBAC roles (ADMINISTRATOR, HCT, VENDOR, STAFF)
- **NotificationChoice / NotificationType** — user notification preferences
- **UserRequest / UserFeedback** — user interactions on tickets
- **Locale / IntlObject** — internationalization support

### Security

- API Platform security annotations on every resource
- Role-based access: `ROLE_ADMINISTRATOR`, `ROLE_HCT`, `ROLE_VENDOR`, `ROLE_STAFF`
- Custom voters: `STORE_POST`, `STORE_GET`, `STORE_EDIT`, `TICKET_POST`, `TICKET_GET`, `TICKET_EDIT`, `USER_EDIT`, `USER_GET`
- JWT authentication via LexikJWTAuthenticationBundle
- Unique active entity validation (soft-delete pattern with `isHide`)

## Frontend: shop-maintenance-front

- **Framework**: Angular 17.3.12 with TypeScript 5.2
- **State management**: NGXS 18.1.1 with storage plugin
- **UI**: Angular Material 17.3.10, Flex Layout, custom theme (`shopm-theme.scss`)
- **Testing**: Jest 29.7.0 (replaces Jasmine/Karma)
- **Build**: Angular CLI 17.3.9, ng-packagr for library
- **Package manager**: Yarn Classic 1.22.22
- **Node**: 18.20.4
- **Components library**: `projects/components-library/` — reusable component library

### Structure

```
src/
  app/
    modules/      # Feature modules (stores, tickets, users, etc.)
    shared/       # Shared components, services, guards, pipes
    store/        # NGXS state (auth, habilitations)
    utils/        # Utility functions
  assets/         # Static files, i18n, extra-vars.js
  environments/   # Environment config
  styles/         # Global SCSS/CSS
```

### Authentication

- Hermes login portal (`https://fed.hermes.com/my.policy`)
- Amazon Cognito for account management
- OAuth2 flow with configurable provider
- JWT decode for client-side token inspection
- `localhost:4200` and integration URL whitelisted for redirect

### Environments

| Environment | Branch | URL | Deployment |
|---|---|---|---|
| Recette Niji | `develop` (back: `integ`) | `https://symfony-integ.nf2500797.niji.cloud` | GCP VM via GitLab CI |
| Preprod client | `stage` | `https://tst-shopmaintenance.hermes.com/` | Client mirroring |
| Production | `master` | `https://shopmaintenance.hermes.com/` | Client mirroring |

### CI/CD

- GitLab CI with 11 stages
- Trivy security scan (filesystem + image)
- SonarQube analysis
- Jest test coverage (full on main branches, affected on feature branches)
- Yarn cache optimization
- Docker image build and push to Artifact Registry

## Internationalization: shop-maintenance-intl

- Separate repo for translation files
- Languages: Arabic (ar), English (en), French (fr), Italian (it), Japanese (jp), Korean (kr), Chinese (zh), plus `zz` (debug/pseudo-localization)
- GitLab CI for validation and deployment
- SonarQube properties template

## Common Infrastructure

### CI/CD Shared Features

- **Artifact Registry**: `europe-west9-docker.pkg.dev`
- **Deployment folder**: `/etc/project/<project-name>/<version>`
- **MySQL**: 5.7
- **Docker Compose**: local development with hermes-specific overrides
- **GitLab CI templates**: includes from `.gitlab/ci/*.yml` (common, package, security-check-image, deploy, audit)

### Development Workflow

- Conventional commits with Husky + Commitlint
- Prettier for code formatting
- TSLint (legacy) + ESLM (intl repo)
- Branch flow: `develop` → `stage` → `master`
- Feature branches: `feat/*` → `develop`
