---
type: entity
title: "ClearID - Symfony API"
status: developing
tags:
  - entity
  - clearid
  - symfony
  - php
  - api
  - postgresql
related:
  - "[[ClearID]]"
  - "[[Hermes International]]"
  - "[[ClearID Integration Architecture]]"
  - "[[clearid-directory]]"
---

# ClearID - Symfony API (clearidapi)

A Symfony 5.4 web application providing a custom API layer for ClearID integrations.

---

## Tech Stack

| Component | Version |
|-----------|---------|
| PHP | >= 7.2.5 |
| Symfony | 5.4.* (LTS) |
| Database | PostgreSQL 14 (default config) |
| ORM | Doctrine 2.13 |
| Frontend | Webpack Encore |
| Queue | Symfony Messenger (Doctrine transport) |
| Tests | PHPUnit 9.5 |

## Key Dependencies

- `amphp/http-client` — Async HTTP client
- `doctrine/doctrine-bundle` — ORM integration
- `sensio/framework-extra-bundle` — Annotations & routing
- `symfony/security-bundle` — Authentication
- `symfony/serializer` — API serialization
- `symfony/webpack-encore-bundle` — Asset pipeline

## Configuration

```dotenv
APP_ENV=dev
APP_SECRET=f024ba425d10c750245df785db0470e1
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=14&charset=utf8"
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```

**Status:** Development environment. The default credentials and `APP_ENV=dev` suggest this is in early development/pilot phase.

## Docker

Includes `docker-compose.yml` and `docker-compose.override.yml` for containerized development.

## Source Structure

```
clearidapi/
├── src/              # Application source (PSR-4: App\\)
├── tests/            # PHPUnit tests
├── assets/           # Webpack assets
├── config/           # Symfony configuration
├── templates/        # Twig templates
├── composer.json     # Dependencies
└── docker-compose.yml
```

## Source

- [[clearid-directory]] — full directory inventory
- `.raw/clearid-directory.md` — raw source
