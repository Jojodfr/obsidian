# CNF (Confie) Repository Analysis

Source: `~/work/cnf/app/source`
Date: 2026-06-02

## Identity

- **Project**: Confie (CNF)
- **Company**: Hermes International
- **Type**: Full-stack web application
- **Framework**: Symfony 7.2 (PHP 8.4)
- **Auth**: Apache mod_auth_openidc / SSO (Mellon)
- **Hosting**: AWS ECS Fargate (containerized)
- **CI/CD**: GitLab CI (17 stages)
- **Repo**: `gitlab.com/hermesintl/cnf`
- **Local path**: `~/work/cnf/app/source`

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Framework | Symfony 7.2 (PHP 8.4) |
| ORM | Doctrine ORM 2.17 + Doctrine Migrations |
| Templates | Twig 3.x |
| Database | PostgreSQL 17 (container), MariaDB mention in config |
| Frontend | Bootstrap, jQuery 3.5, dataTables.js |
| PDF | wkhtmltopdf + dompdf |
| Excel/CSV | PhpSpreadsheet, League CSV |
| Barcode | picqer/php-barcode-generator |
| Auth | Apache mod_auth_openidc (SSO) |
| AWS | aws-sdk-php-symfony, S3 file handling |
| Container | Debian 12 Bookworm, Apache 2.4, PHP 8.4, PostgreSQL 17 |
| Build | Kaniko (container image build) |

## Repository Structure

```
cnf/app/source/
├── .gitlab-ci.yml              # Main CI/CD config
├── gitlab-ci-custom.yml        # Custom overrides (Vault, Kaniko, ECS)
├── composer.json               # PHP dependencies (Symfony 7.2)
├── Dockerfile                  # Debian 12 + Apache + PHP 8.4 + PostgreSQL
├── docker-compose.yml          # Local dev: PostgreSQL 15
├── config/bundles.php          # Symfony bundles
├── src/                        # Full PHP source code
│   ├── Command/                # CLI commands (imports, mail, notifications)
│   ├── Controller/             # Controllers (Admin, BO, Prets, Rapports...)
│   ├── Entity/                 # Doctrine entities (Cnf01t-Cnf27t, Adm*, User...)
│   ├── Form/                   # Symfony forms
│   ├── Repository/             # Doctrine repositories
│   ├── Security/               # Auth voters, authenticator
│   ├── Service/                # Business logic (Catalogue, Pret, PDF, Mail, M3...)
│   ├── EventListener/          # Exception listener
│   ├── EventSubscriber/        # Locale, user subscribers
│   ├── Mailer/                 # Mail service
│   ├── Twig/                   # Twig extensions
│   └── Kernel.php
├── templates/                  # Twig templates
├── public/                     # Web root
├── assets/                     # Static assets
├── migrations/                 # Doctrine migrations
├── environments/               # Per-env config
│   ├── chk/env.properties
│   └── prd/env.properties
├── tests/                      # PHPUnit tests
├── translations/               # i18n
└── docker/                     # Docker configs (Apache, PHP, cron, supervisor)
```

## CI/CD Pipeline (Complete)

### All 17 Stages

```
init -> build -> test -> analysis -> manifest -> sign -> verify -> push -> publish -> schedule -> pre_deploy -> deploy_chk -> promote -> release -> deploy_prd -> status -> delete
```

### Environment Matrix

| Env | Enabled | Trigger | AWS Account | ECS Service | SSM Path |
|-----|---------|---------|-------------|-------------|----------|
| DEV | false | - | - | - | - |
| TST | false | - | - | - | - |
| CHK | true | MR / Branch | 543025552220 | confie-chk-fargate | /confie/chk/service/confie-chk-fargate |
| ACC | false | - | - | - | - |
| PRD | true | Git tag | 027354322115 | confie-prd-fargate | /confie/prd/service/confie-prd-fargate |

### CI Components (Hermes Template Library)

**`.gitlab-ci.yml` (main config)**:
- **Baseline component** (`baseline-component/baseline@~latest`)
  - Inputs: sonarqube_stage=`analysis`, language=`container`, tool=`kaniko`, language_version=`1.0.0-amd64`
- **ECS Deployment component** (`complete-ecs-service-deployment@feat/jfrog-migration`) x2
  - CHK: service_name=`confie-chk`, account=`543025552220`
  - PRD: service_name=`confie-prd`, account=`027354322115`
  - Both: enable_secret_manager=`true`, env_variables_file=`environments/<env>/env.properties`
- **Custom**: `gitlab-ci-custom.yml` (local include)

**`gitlab-ci-custom.yml` (custom overrides)**:
- **Vault component** (`vault-component/vault@~latest`)
  - Retrieves secrets via `load_secret` command
- **ECS Deployment component** (duplicate of main for custom rules)
- **Custom build jobs**: overrides `kaniko_build` with `custom_kaniko_build`
- **Custom deploy jobs**: `confie-chk:ecr-push-image`, `confie-chk:ecs-deploy-service`, `confie-chk:ecs-push-task-definition`

### Key Variables

```yaml
PUBLISH_REPOSITORY: artifactory
GITGUARDIAN_FULL_SCAN: "true"
DOCKER_TLS_CERTDIR: "/certs"
GIT_SSL_NO_VERIFY: 'true'
DEV_ENABLED: "false"
TST_ENABLED: "false"
CHK_ENABLED: "true"
ACC_ENABLED: "false"
```

### Custom Jobs (gitlab-ci-custom.yml)

**get-secrets-rct** (CHK):
- Extends: `.get-secrets-template`
- Environment: `chk`
- Rules: branch commits, MR events
- Excludes: release commits (`chore(release)`), tags

**get-secrets-prd** (PRD):
- Extends: `.get-secrets-template`
- Environment: `prd`
- Rules: Git tags only

**custom_kaniko_build**:
- Extends: `kaniko_build` (baseline disabled with `when: never`)
- Rules: branch commits, MR events
- Script: writes CI metadata to files (`CI_PIPELINE_URL`, `CI_COMMIT_SHA`, etc.) then calls base kaniko script

**confie-chk:ecr-push-image:chk**:
- Stage: `deploy_chk`
- Pushes container image to ECR

**confie-chk:ecs-deploy-service:chk**:
- Stage: `deploy_chk`
- Deploys ECS service

**confie-chk:ecs-push-task-definition:chk**:
- Stage: `deploy_chk`
- Pushes ECS task definition

### Security Stack

| Tool | Stage | Purpose |
|------|-------|---------|
| GitGuardian | init | Full repository secret scanning |
| SonarQube | analysis | Code quality (projectKey: com.hermes:cnf.source) |
| Vault | init | Secret retrieval via `load_secret` |
| JFrog | push/publish | Artifact storage (container images) |
| AWS OIDC | deploy | Credential-less AWS authentication |

### Build Process

1. **Baseline init**: GitGuardian scan, Vault secrets
2. **custom_kaniko_build**: Build Docker image with Kaniko
   - Debian 12 base from internal Harbor (`prd-harbor.atlas.hermes`)
   - Apache 2.4 + PHP 8.4 + PostgreSQL 17 + wkhtmltopdf + Composer
   - App source copied to `/var/www/html/`
   - `composer install --no-scripts --no-dev`
3. **sign/verify/manifest**: Image signing and verification
4. **push/publish**: JFrog Artifactory registry
5. **deploy_chk**: ECS service deployment to CHK
   - ECR push image
   - Push task definition
   - Deploy service
6. **deploy_prd**: ECS service deployment to PRD (manual gate after promote/release)

### Container Architecture

```
Debian 12 (Bookworm)
  Apache 2.4 (ports 80)
    mod_auth_openidc (SSO)
    mod_rewrite
    mod_remoteip
    mod_php8.4
  PHP 8.4-FPM/Apache
    Symfony 7.2 app
    PostgreSQL 17 client
    wkhtmltopdf 0.12.6
    Composer
  PostgreSQL 17 (server, optional)
  Supervisor (process manager)
  Cron
```

User: `www-efs` (UID 1001) for EFS access

## Key Features

- **Back-Office Admin**: User/group/menu management, reference data tables
- **Loans (Prets)**: Loan/catalog management with dataTables
- **Reports (Rapports)**: Multiple report types with PDF/Excel export
- **Import/Export**: Excel/CSV data import and export
- **Barcode Generation**: For labeling
- **PDF Generation**: wkhtmltopdf + dompdf
- **M3 API Integration**: Stock verification via ESB
- **AWS S3**: File upload/download
- **DAM Integration**: Digital Asset Management
- **Mail**: Symfony Mailer with MailHog for local dev
- **SSO**: Apache mod_auth_openidc with Mellon

## Comparison with HB8 / KLM

| Aspect         | HB8                     | KLM                 | CNF (Confie)           |
| -------------- | ----------------------- | ------------------- | ---------------------- |
| Name           | Haute Bijouterie 8      | Kellymorphose       | Confie                 |
| Framework      | Nuxt.js 3               | Vue.js SPA          | Symfony 7.2            |
| Language       | JavaScript              | JavaScript          | PHP 8.4                |
| Source in repo | Yes (built)             | No (compiled only)  | Yes (full source)      |
| Hosting        | AWS S3 static           | AWS S3 static       | AWS ECS Fargate        |
| Build          | Node.js build           | Static files        | Kaniko container build |
| Auth           | JWT client-side         | Hermes SSO/OAuth2   | Apache mod_auth_openidc|
| Database       | None (static)           | None (static)       | PostgreSQL/MariaDB     |
| Environments   | 5 (DEV/TST/CHK/ACC/PRD) | 2 (CHK/PRD)         | 2 (CHK/PRD)            |
| CI Stages      | 20                      | 17                  | 17                     |

All three share the same Hermes CI/CD template library, Vault secrets, JFrog Artifactory, and AWS OIDC auth.
