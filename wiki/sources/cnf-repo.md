---
type: source
title: "Confie (CNF) Repository"
created: 2026-06-02
updated: 2026-06-02
source: ".raw/cnf-repo.md"
status: mature
tags:
  - source
  - repo
  - gitlab
  - hermes
  - symfony
  - php
  - cicd
  - ecs
  - fargate
related:
  - "[[hb8-app-repo]]"
  - "[[klm-repo]]"
  - "[[Hermes International]]"
  - "[[Confie]]"
  - "[[Symfony ECS Deployment]]"
---

# Confie (CNF) Repository

Navigation: [[sources/_index|Sources]] | [[index]] | [[hb8-app-repo]] | [[klm-repo]]

Hermes International GitLab repository for the **Confie** web application. Symfony 7.2 PHP application deployed to **AWS ECS Fargate** via GitLab CI/CD with Kaniko container builds.

---

## Quick Facts

| Attribute | Value |
|-----------|-------|
| **Project** | Confie (CNF) |
| **Company** | Hermes International |
| **Type** | Full-stack web application |
| **Framework** | Symfony 7.2 (PHP 8.4) |
| **Auth** | Apache mod_auth_openidc / SSO |
| **Hosting** | AWS ECS Fargate (containerized) |
| **CI/CD** | GitLab CI (17 stages) |
| **Repo** | `gitlab.com/hermesintl/cnf` |
| **Local path** | `~/work/cnf/app/source` |

---

## Technology Stack

| Layer | Technology | Evidence |
|-------|-----------|----------|
| Framework | Symfony 7.2 | `composer.json` requires `symfony/framework-bundle ^7.2` |
| Language | PHP 8.4 | `composer.json` requires `php >=8.2.3`, Dockerfile installs `php8.4` |
| ORM | Doctrine ORM 2.17 | `doctrine/orm ^2.17.1`, migrations, entities in `src/Entity/` |
| Templates | Twig 3.x | `twig/twig ^2.12|^3.0`, `templates/` directory |
| Database | PostgreSQL 17 | Dockerfile installs `postgresql-17`, `docker-compose.yml` uses postgres |
| Frontend | Bootstrap + jQuery 3.5 | `README.md`, `public/js/`, `public/css/` |
| Tables | dataTables.js | `README.md` documents dataset attributes |
| PDF | wkhtmltopdf + dompdf | `knplabs/knp-snappy-bundle`, `dompdf/dompdf ^3.1` |
| Excel/CSV | PhpSpreadsheet + League CSV | `phpoffice/phpspreadsheet ^1.29`, `league/csv ^9.11` |
| Barcode | picqer/php-barcode-generator | `picqer/php-barcode-generator ^3.2` |
| Auth | Apache mod_auth_openidc | Dockerfile installs `libapache2-mod-auth-openidc` |
| AWS SDK | aws-sdk-php-symfony | `aws/aws-sdk-php-symfony ^2.8`, S3 file handling |
| Container | Debian 12 + Apache + PHP | `FROM debian:bookworm`, Apache 2.4, Supervisor |
| Build | Kaniko | `.gitlab-ci.yml` inputs: `language: container`, `tool: kaniko` |

---

## Repository Structure

```
cnf/app/source/
├── .gitlab-ci.yml              # Main CI/CD config
├── gitlab-ci-custom.yml        # Custom overrides
├── composer.json               # PHP dependencies (Symfony 7.2)
├── Dockerfile                  # Debian 12 + Apache + PHP 8.4 + PostgreSQL
├── docker-compose.yml          # Local dev: PostgreSQL 15
├── config/bundles.php          # Symfony bundles
├── src/                        # Full PHP source code
│   ├── Command/                # CLI commands (imports, mail, notifications)
│   ├── Controller/             # Controllers (Admin, BO, Prets, Rapports, Catalogue)
│   ├── Entity/                 # Doctrine entities (Cnf01t-Cnf27t, Adm*, User, Igg001t)
│   ├── Form/                   # Symfony forms
│   ├── Repository/             # Doctrine repositories
│   ├── Security/               # Auth voters, authenticator
│   ├── Service/                # Business logic (Catalogue, Pret, PDF, Mail, M3, S3)
│   ├── EventListener/          # Exception listener
│   ├── EventSubscriber/        # Locale, user subscribers
│   ├── Mailer/                 # Mail service
│   ├── Twig/                   # Twig extensions
│   └── Kernel.php
├── templates/                  # Twig templates
├── public/                     # Web root
├── assets/                     # Static assets
├── migrations/                 # Doctrine migrations
├── environments/               # Per-env config files
│   ├── chk/env.properties
│   └── prd/env.properties
├── tests/                      # PHPUnit tests
├── translations/               # i18n
└── docker/                     # Docker configs (Apache, PHP, cron, supervisor)
```

**Key observation**: Unlike KLM, this repo contains the **full PHP source code**. It is not a compiled/dist-only repository.

---

## CI/CD Pipeline (Complete)

### All 17 Stages

```
init → build → test → analysis → manifest → sign → verify → push → publish → schedule → pre_deploy → deploy_chk → promote → release → deploy_prd → status → delete
```

### Environment Matrix

| Env | Enabled | Trigger | AWS Account | ECS Service | SSM Path |
|-----|---------|---------|-------------|-------------|----------|
| DEV | false | - | - | - | - |
| TST | false | - | - | - | - |
| CHK | true | MR / Branch | 543025552220 | confie-chk-fargate | `/confie/chk/service/confie-chk-fargate` |
| ACC | false | - | - | - | - |
| PRD | true | Git tag | 027354322115 | confie-prd-fargate | `/confie/prd/service/confie-prd-fargate` |

### CI Components (Hermes Template Library)

**`.gitlab-ci.yml` (main config)**:
- **Baseline component** (`baseline-component/baseline@~latest`)
  - Provides core stages: init, build, test, analysis, manifest, sign, verify, push, publish, schedule
  - Inputs: sonarqube_stage=`analysis`, language=`container`, tool=`kaniko`, language_version=`1.0.0-amd64`
- **ECS Deployment component** (`complete-ecs-service-deployment@feat/jfrog-migration`) x2
  - CHK: service_name=`confie-chk`, environment=`chk`, aws_account=`543025552220`, ssm_path=`/confie/chk/service/confie-chk-fargate`
  - PRD: service_name=`confie-prd`, environment=`prd`, aws_account=`027354322115`, ssm_path=`/confie/prd/service/confie-prd-fargate`
  - Both: enable_secret_manager=`true`, env_variables_file=`environments/<env>/env.properties`
  - CHK has custom_rules for branch commits; PRD uses default tag rules
- **Custom**: `gitlab-ci-custom.yml` (local include)

**`gitlab-ci-custom.yml` (custom overrides)**:
- **Vault component** (`vault-component/vault@~latest`)
  - Retrieves secrets via `load_secret` command
  - Secrets injected as CI variables via `dotenv` report
- **ECS Deployment component** (CHK only, with commented-out custom_rules)
- **Custom build jobs**: `kaniko_build` disabled (`when: never`), replaced by `custom_kaniko_build`
- **Custom deploy jobs**: `confie-chk:ecr-push-image:chk`, `confie-chk:ecs-deploy-service:chk`, `confie-chk:ecs-push-task-definition:chk`

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
- Rules: branch commits, MR events (excludes tags and releases)
- Script: writes CI metadata to files (`CI_PIPELINE_URL`, `CI_COMMIT_SHA`, `CI_PROJECT_URL`, `CI_COMMIT_TAG`, `CI_COMMIT_TIMESTAMP`, `CI_COMMIT_URL`) then calls base kaniko script via `!reference [kaniko_build, script]`

**confie-chk:ecr-push-image:chk**:
- Stage: `deploy_chk`
- Rules: branch commits, MR events (excludes tags and releases)
- Pushes container image to ECR

**confie-chk:ecs-deploy-service:chk**:
- Stage: `deploy_chk`
- Rules: branch commits, MR events (excludes tags and releases)
- Deploys ECS service

**confie-chk:ecs-push-task-definition:chk**:
- Stage: `deploy_chk`
- Rules: branch commits, MR events (excludes tags and releases)
- Pushes ECS task definition

### Build Process

1. **Baseline init**: GitGuardian scan, Vault secrets retrieval
2. **custom_kaniko_build**: Build Docker image with Kaniko
   - Base: Debian 12 (Bookworm) from `prd-harbor.atlas.hermes/hub.docker.com/library/debian:bookworm`
   - Stack: Apache 2.4 + PHP 8.4 + PostgreSQL 17 + wkhtmltopdf + Composer
   - App source copied to `/var/www/html/`
   - `composer install --no-scripts --no-dev`
   - CI metadata files written to `public/`
3. **test**: PHPUnit tests
4. **analysis**: SonarQube code quality
5. **sign/verify/manifest**: Image signing and verification
6. **push/publish**: JFrog Artifactory registry
7. **schedule**: (if applicable)
8. **pre_deploy**: Pre-deployment checks
9. **deploy_chk**: ECS service deployment to CHK
   - ECR push image
   - Push task definition
   - Deploy service
10. **promote/release**: Promotion gates
11. **deploy_prd**: ECS service deployment to PRD (after manual gate)
12. **status/delete**: Post-deployment cleanup

### Container Architecture

```
Debian 12 (Bookworm)
  Apache 2.4 (port 80)
    mod_auth_openidc  (SSO)
    mod_rewrite
    mod_remoteip
    mod_php8.4
  PHP 8.4
    Symfony 7.2 app
    PostgreSQL 17 client
    wkhtmltopdf 0.12.6
    Composer
  PostgreSQL 17 (server)
  Supervisor (process manager)
  Cron
```

- User: `www-efs` (UID 1001) for EFS access
- Group: `www-data`
- Entrypoint: `/usr/local/bin/supervisor-startup.sh`

### Security Stack

| Tool | Stage | Purpose |
|------|-------|---------|
| GitGuardian | init | Full repository secret scanning |
| SonarQube | analysis | Code quality baseline (projectKey: `com.hermes:cnf.source`) |
| Vault | init | Secret retrieval via `load_secret` |
| JFrog | push/publish | Artifact storage (`PUBLISH_REPOSITORY: artifactory`) |
| AWS OIDC | deploy | Credential-less AWS authentication |

### SonarQube Configuration

- **projectKey**: `com.hermes:cnf.source`
- **Sources**: `config, public, src, templates`
- **Exclusions**: `public/fontawesome/**`, `public/css/jquery**`, `public/js/jquery**`, `public/js/pdfmake**`, `public/js/jszip**`, `public/js/dataTables**`, `public/css/bootstrap.css`, `public/js/buttons.html5.js`, `public/js/bootstrap.js`
- **Ignored rules**: CSS font declarations, pseudo-elements, duplicate properties, shorthand overrides; PHP commented-out code, TODO tags

---

## Docker & Container Architecture

### Overview

Confie runs as a **single monolithic container** on AWS ECS Fargate. The image is built with **Kaniko** in CI and contains the full LAMP-like stack (Apache + PHP + PostgreSQL) plus application code.

### Base Image

```dockerfile
FROM prd-harbor.atlas.hermes/hub.docker.com/library/debian:bookworm
```

Uses Hermes internal Harbor registry as a pull-through cache for Docker Hub. Debian 12 (Bookworm) provides the stable foundation.

### Build Stages (Dockerfile)

| Step | What Happens |
|------|-------------|
| 1. APT setup | `ca-certificates`, update trust store |
| 2. APT tuning | Disable docs (`/etc/dpkg/dpkg.cfg.d/01_nodoc`) and recommends (`99no-recommends`) to reduce image size |
| 3. System packages | `apache2`, `libapache2-mod-auth-openidc`, `msmtp`, `xvfb`, `wget`, `gettext`, `openssl`, `pwgen`, `cron`, `supervisor`, `unzip`, `curl` |
| 4. PHP 8.4 | Adds Sury PHP repo (`packages.sury.org/php`), installs `php8.4` + 12 extensions (xml, mbstring, pgsql, gd, opcache, posix, zip, mcrypt, intl, curl) |
| 5. PostgreSQL 17 | Adds PGDG repo (`apt.postgresql.org`), installs `postgresql-17` server + client |
| 6. wkhtmltopdf | Downloads `wkhtmltox_0.12.6.1-3.bookworm_amd64.deb` from GitHub releases, installs |
| 7. Composer | `curl` installer to `/usr/local/bin/composer` |
| 8. Apache tuning | `a2dismod ssl`, `a2enmod headers remoteip rewrite status php8.4` |
| 9. User creation | `useradd --uid 1001 www-efs`, adds to `www-data` group (for EFS access) |
| 10. App copy | Individual `COPY --chown=www-efs:www-efs` for each dir: `assets/`, `bin/`, `config/`, `debug/`, `environments/`, `migrations/`, `public/`, `src/`, `templates/`, `tests/`, `translations/` |
| 11. Composer install | Run as `www-efs`: `composer install --no-scripts --no-dev --no-cache` |
| 12. CI metadata | Copy `CI_PIPELINE_URL`, `CI_COMMIT_SHA`, `CI_COMMIT_TAG`, `CI_COMMIT_TIMESTAMP`, `CI_COMMIT_URL`, `CI_PROJECT_URL` to `/var/www/html/public/` |
| 13. Config copy | Apache vhost templates, PHP `.ini` files, supervisor config, cron files, startup scripts |
| 14. Permissions | `chmod 775 /var/www/html`, `chmod o+x` on parent dirs, Symfony `var/` ownership |
| 15. Entrypoint | `CMD ["/usr/local/bin/supervisor-startup.sh"]` |

### Apache Configuration

| Module | Status | Purpose |
|--------|--------|---------|
| `ssl` | disabled | SSL termination at load balancer |
| `headers` | enabled | HTTP header manipulation |
| `remoteip` | enabled | Proxy/load balancer IP forwarding |
| `rewrite` | enabled | Symfony URL rewriting |
| `status` | enabled | Server status page |
| `php8.4` | enabled | PHP module |
| `auth_openidc` | installed | SSO via OpenID Connect (Microsoft Entra ID) |

Apache runs on **port 80 only** (no HTTPS in container). SSL is terminated at the AWS ALB.

### Runtime Startup Flow (`supervisor-startup.sh`)

1. **Read env vars** from ECS task definition / SSM / Secrets Manager
2. **Generate `.env` file** via `envsubst` from `/root/.env.aws.tpl`
   - Substitutes: DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD, AWS_BUCKET_NAME, AWS_BASE_URL, AWS_DEFAULT_REGION, DBNAME, APP_LAUNCH_URL, CI_ENV, APP_URL, DAM_URL, DAM_USERNAME, DAM_PASSWORD, MAIL_BCC_RCT
3. **Run `composer dump-env`** to generate `.env.local.php` for production performance
4. **Write AWS env file** to `/run/aws-env`
5. **Generate health check** PHP script with DB connection params
6. **Configure Apache vhost** via `envsubst` from `vhost-default.conf.tpl`
7. **Configure OIDC** via `envsubst` from `auth-oauth2.conf.tpl`
   - Provider: `login.microsoftonline.com` (Microsoft Entra ID)
   - Client ID/Secret from SSM
   - Downloads OIDC metadata and tests Microsoft endpoints
8. **Test DAM connectivity** (ESB endpoints for Digital Asset Management)
9. **Run Doctrine migrations**:
   ```
   doctrine:migration:sync-metadata-storage
   doctrine:migrations:current
   doctrine:migrations:up-to-date
   doctrine:migrations:list
   doctrine:migrations:latest
   doctrine:migrations:migrate --no-interaction
   doctrine:migrations:status
   doctrine:schema:validate
   ```
10. **Sanitize secrets** from env (mask passwords, unset sensitive vars)
11. **Check EFS mount** (`mount | grep 127.0.0.1`)
12. **Set up EFS directories** (`sessions/`, `images/`) with symlinks to `public/`
13. **Configure cron** (`crontab -u www-efs` from `/root/cron`)
14. **Fix log permissions** (Apache, PHP, cron, Symfony logs)
15. **Start Supervisor** (`supervisord -n -c /etc/supervisord.conf`)

### Supervisor Process Model

Supervisor runs as **PID 1** inside the container (replacing systemd/init) because ECS Fargate containers have no init system. The config is at `/etc/supervisord.conf`, copied from `docker/etc/supervisord.conf` during the image build.

#### Why Supervisor

| Approach | Problem | Why Supervisor Wins |
|----------|---------|---------------------|
| Direct `CMD apache2` | No process management; container dies if Apache crashes | Supervisor restarts failed processes |
| `systemd` | Not available in containers; requires privileged mode | Supervisor is designed for containers |
| Shell script with `&` + `wait` | No log rotation, no restart policy, zombie processes | Supervisor handles all of these |

#### Supervisor Global Config

```ini
[supervisord]
nodaemon=true              ; Run in foreground (required for Docker)
user=root                  ; Start as root, drop privileges per-program
stdout_logfile=/dev/stdout ; Default: route child stdout to container stdout
stderr_logfile=/dev/stderr ; Default: route child stderr to container stderr
stdout_maxbytes=0          ; Disable log rotation (CloudWatch handles it)
stderr_maxbytes=0
```

Key settings for ECS: `nodaemon=true` keeps Supervisor in the foreground so Docker does not exit. Logs go to `/dev/stdout` and `/dev/stderr` so ECS daemon streams them to **CloudWatch Logs**.

#### Managed Programs (6 processes)

| Program | Command | User | Autostart | Autorestart | Killasgroup |
|---------|---------|------|-----------|-------------|-------------|
| `cron` | `/usr/sbin/cron -f -L 15` | root | true | true | false |
| `apache` | `/usr/local/bin/apache-startup.sh` | root | true | true | **true** |
| `apache2-error` | `tail -f /var/log/apache2/error.log` | root | true | true | false |
| `apache2-vhost-error` | `tail -f /var/log/apache2/error_log` | root | true | true | false |
| `apache2-vhost-access` | `tail -f /var/log/apache2/access_log` | root | true | true | false |
| `php-error` | `tail -f /var/log/php.log` | root | true | true | false |

**`apache` program details:**
- `stopsignal=6` (SIGABRT) -- forces Apache to dump core and exit cleanly on stop
- `killasgroup=true` + `stopasgroup=true` -- ensures Apache and all its worker processes are terminated together
- Runs `/usr/local/bin/apache-startup.sh` which sources envvars, runs configtest, then launches `apache2 -DFOREGROUND`

**Log tailer programs (`apache2-error`, `apache2-vhost-error`, `apache2-vhost-access`, `php-error`):**
- Each runs `bash -c 'sleep 5 && tail -f /var/log/...'`
- The `sleep 5` prevents race conditions where tail starts before Apache creates the log file
- `stdout_logfile=/proc/self/fd/1` -- writes directly to container stdout (bypassing Supervisor's default log handling)
- `stdout_maxbytes=0` + `stderr_maxbytes=0` -- disables Supervisor's internal log rotation (CloudWatch handles rotation)
- This is the **sidecar pattern in a single container**: multiple `tail -f` processes act as log shippers

#### Log Flow to CloudWatch

```
Apache / PHP / Cron write to /var/log/...
    |
    +---> Supervisor tail processes read with tail -f
              |
              +---> Write to /proc/self/fd/1 (container stdout)
                        |
                        +---> ECS Docker daemon captures stdout
                                  |
                                  +---> CloudWatch Logs (log group: /ecs/confie-...)
```

Without the tail programs, Apache logs would stay inside the container and be lost when the task stops.

#### Supervisor vs. ECS Task Health Checks

Health checking operates at **three layers** in the Confie stack:

| Layer | Mechanism | What It Checks | Failure Action |
|-------|-----------|----------------|----------------|
| Process | Supervisor | Is Apache/cron running? | Restart process inside container |
| Task | ECS container health check | `health_check.php` returns OK? | Stop task, start new one |
| Load Balancer | ALB target group | HTTP 200 from `/health_check.php`? | Deregister target, route to healthy task |

---

### Health Check Endpoint (`health_check.php`)

Located at `/var/www/html/public/health_check.php` (copied from `docker/var-www/` at build time). This is the primary health probe for both ECS and ALB.

#### How It Works

The script requires `health_check_ini.php` (generated at container startup by `supervisor-startup.sh`) which injects runtime database credentials:

```php
require_once 'health_check_ini.php';  // Contains $DB_SERVER, $DB_USER, $DB_PASSWORD, etc.
```

**Checks performed (in order):**

1. **Database TCP connectivity** (`fsockopen` to `$DB_SERVER:5432`)
   - Timeout: 5 seconds
   - PostgreSQL port 5432 (MySQL code exists but is commented out)
   - If this fails, the entire health check returns `KO`

2. **Database query test** (`pg_connect` + `pg_query`)
   - Connection string: `host=$DB_SERVER port=5432 dbname=$DB_DATABASE user=$DB_USER password=$DB_PASSWORD connect_timeout=5 options='--client_encoding=UTF8'`
   - Force new connection: `PGSQL_CONNECT_FORCE_NEW`
   - Runs: `SELECT * FROM user;`
   - If connection or query fails, returns `KO`

3. **SMTP server check**
   - DNS resolution of `$SMTP_SERVER` (default: `smtp.atlas.hermes`)
   - TCP connection test on port 25
   - If DNS fails or port is unreachable, returns `KO`

4. **Container start time**
   - Reads `$START_TIME` from `health_check_ini.php`
   - Converts from UTC to Europe/Paris timezone for display

#### Response Format

| Health | Body | HTTP Status |
|--------|------|-------------|
| Healthy | `OK` + start time | 200 |
| Unhealthy | `KO` + diagnostic messages | 200 (script handles its own status) |

**Debug mode:** Append `?debug` to see full diagnostic output including `$_SERVER`, check results, and start time regardless of health state.

#### `health_check_ini.php` Generation

This file is generated at container startup by `supervisor-startup.sh`:

```bash
echo -e "<?php\n\$DB_SERVER = \"$DB_SERVER\";\n\$DB_USER = \"$DB_USER\";\n\$DB_PASSWORD = \"$DB_PASSWORD\";\n\$SMTP_SERVER = \"$SMTP_SERVER\";\n\$DB_DATABASE = \"$DB_DATABASE\";\n\$DB_PORT = \"$DB_PORT\";\n\$START_TIME=\"$DATE\" ;\n" > /var/www/html/public/health_check_ini.php
```

- Sourced from environment variables injected by ECS (from SSM Parameter Store / Secrets Manager)
- Regenerated on every container start (so credentials rotate automatically)
- Contains the actual database password in plaintext (protected by filesystem permissions)

#### Commented-Out Checks

The health check file contains extensive commented code for additional checks that are not currently active:

| Check | Status | Reason |
|-------|--------|--------|
| MySQL connectivity | Commented | App uses PostgreSQL |
| OIDC provider reachability | Commented | `login.microsoftonline.com` connectivity test |
| EFS mount validation | Commented | File existence check on `/documents/test_efs.txt` |
| Microsoft Graph API | Commented | `graph.microsoft.com`, `graph.windows.net`, `pas.windows.net` DNS + TCP tests |
| `sts.windows.net` | Commented | Microsoft Security Token Service |

These are likely disabled because they test external dependencies that may have transient failures not indicative of application health.

### ECS Task Definition Health Check

The `complete-ecs-service-deployment` component configures the ECS task with health checks. The container is marked unhealthy if `health_check.php` returns `KO`, triggering ECS to stop and replace the task.

### ALB Target Group Health Check

The Application Load Balancer polls `/health_check.php` on each target:
- **Healthy threshold:** 2 consecutive successes
- **Unhealthy threshold:** 2 consecutive failures
- **Interval:** 30 seconds
- **Timeout:** 5 seconds

When unhealthy, the ALB stops sending traffic to that ECS task but keeps it running (giving Supervisor a chance to recover). If ECS also marks it unhealthy, the task is replaced.

### Failure Cascade

```
Database goes down
    |
    +---> health_check.php returns KO
              |
              +---> ALB deregisters target (stops traffic)
              +---> ECS marks task unhealthy
                        |
                        +---> ECS stops task
                        +---> ECS starts new task on another host
                                  |
                                  +---> New task runs supervisor-startup.sh
                                  +---> New task tries migrations, fails (DB still down)
                                  +---> health_check.php returns KO
                                  +---> Cycle repeats until DB recovers
```

This design ensures **no traffic hits a task that cannot serve requests**, even if the process itself is still running.

#### Cron Under Supervisor

The `cron` program runs `/usr/sbin/cron -f -L 15`:
- `-f` = foreground mode (required for Supervisor)
- `-L 15` = log level 15 (debug level for cron)
- Cron jobs are loaded from `/root/cron` via `crontab -u www-efs` during startup
- The cron file is copied from `docker/cron` at image build time

#### Startup Order

Supervisor starts all 6 programs **simultaneously** (no explicit priority ordering), but:
1. `apache-startup.sh` takes ~10-15 seconds to complete (migrations, config generation, connectivity tests)
2. The tail processes `sleep 5` before starting to wait for log files
3. Cron starts immediately and is ready to accept jobs
4. If Apache fails to start, Supervisor retries up to the configured `startretries` (default 3)

### Container User Model

| User | UID | Groups | Purpose |
|------|-----|--------|---------|
| `www-efs` | 1001 | `www-efs`, `www-data` | Primary app user; owns all files; used for EFS access |
| `www-data` | 33 | `www-data`, `www-efs` | Apache runtime group |
| `root` | 0 | - | Runs Supervisor, cron, startup scripts |

Apache runs as `www-efs` (set via `sed -i` in startup script replacing `www-data` in `/etc/apache2/envvars`).

### docker-compose.yml (Local Dev)

```yaml
version: '3'
services:
  database:
    image: postgres:${POSTGRES_VERSION:-15}-alpine
    environment:
      POSTGRES_DB: ${POSTGRES_DB:-app}
      POSTGRES_PASSWORD: ${POSTGRES_PASSWORD:-!ChangeMe!}
      POSTGRES_USER: ${POSTGRES_USER:-app}
    volumes:
      - database_data:/var/lib/postgresql/data:rw
volumes:
  database_data:
```

Local development uses **PostgreSQL 15** (Alpine). Production container has **PostgreSQL 17** installed but typically connects to an external RDS/Aurora instance.

### docker/ Directory

| File/Dir | Purpose |
|----------|---------|
| `apache-startup.sh` | Apache foreground launcher (used by supervisor) |
| `apache-startup-full.sh` | Extended startup with extra diagnostics |
| `supervisor-startup.sh` | Main entrypoint: env setup, migrations, supervisor |
| `etc/supervisord.conf` | Supervisor config with 6 managed programs |
| `etc-php.d/` | PHP `.ini` overrides (custom settings 94-99) |
| `etc-apache2-conf-enabled/` | Apache conf snippets |
| `etc-apache2-mellon/` | Mellon/SAML auth configs |
| `vhost-default.conf.tpl` | Apache vhost template (envsubst) |
| `auth-oauth2.conf.tpl` | OpenID Connect auth template (envsubst) |
| `msmtprc.tpl` | SMTP relay config template |
| `.env.aws.tpl` | Symfony `.env` template for AWS environments |
| `cron` / `cron-custom` | Cron job definitions for www-efs |
| `var-www/` | Static files: `robots.txt`, `index.html`, `health_check.php` |
| `var-www-auth/` | Auth redirect handler |
| `scripts/` | Helper scripts: `aws-config.sh`, `before-start.sh`, `push.sh`, `run.sh`, `scan-image.sh` |
| `scripts/deploy/` | ECS deployment scripts |

### Security Hardening in Container

1. **SSL disabled in Apache** -- TLS terminated at AWS ALB, no cert management in container
2. **Non-root app user** (`www-efs`) owns all application files
3. **Secrets masked in logs** -- passwords truncated to `**` before display
4. **Sensitive env vars unset** after use (`OIDC_CLIENT_SECRET`, `DB_PASSWORD`, `SUBSTVARS`)
5. **No dev dependencies** -- `composer install --no-dev`
6. **Health check endpoint** (`health_check_ini.php`) for ECS task health checks
7. **EFS isolation** -- sessions and images on separate EFS mount points

### Image Size Optimizations

- `--no-install-recommends` via apt config
- `--no-doc` via dpkg config
- No source maps or dev tools in production image
- Individual directory COPY instead of full context COPY

---

## Key Features

- **Back-Office Admin**: User/group/menu management (`AdmUser`, `AdmGroupe`, `AdmMenu`), reference data tables (`AdmRefData`, `AdmRefEntity`)
- **Loans (Prets)**: Loan/catalog management with dataTables (`Cnf01t`-`Cnf27t` entities)
- **Reports (Rapports)**: Multiple report types with PDF/Excel export
- **Catalogue**: Product/item catalog with barcode generation
- **Import/Export**: Excel/CSV data import and export via `ImportExcelController`, `ImportService`, `PhpSpreadsheetService`
- **PDF Generation**: `PDFService` using wkhtmltopdf + dompdf + KnpSnappyBundle
- **M3 API Integration**: Stock verification via `M3Service` and ESB credentials
- **AWS S3**: File upload/download via `S3ClientService`
- **DAM Integration**: Digital Asset Management (`DAM_URL`, `DAM_USERNAME`, `DAM_PASSWORD`)
- **Mail**: Symfony Mailer with MailHog for local dev
- **SSO**: Apache mod_auth_openidc with Mellon (`AUTH_SERVEUR_APACHE`)
- **CLI Commands**: `FetchImagesCommand`, `ImportDataCommand`, `ImportPieceCommand`, `SendMail*` commands, `ValidReservationCommand`

---

## CI/CD Diagram

Static version:

![[cnf-cicd-pipeline.svg]]

> **Interactive version**: Open `wiki/canvases/cnf-cicd-pipeline.canvas` in Obsidian for a fully interactive diagram with clickable nodes linked to wiki pages.

---

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
| Database       | None (static)           | None (static)       | PostgreSQL / MariaDB   |
| Environments   | 5 (DEV/TST/CHK/ACC/PRD) | 2 (CHK/PRD)         | 2 (CHK/PRD)            |
| CI Stages      | 20                      | 17                  | 17                     |
| Deployment     | S3 sync / CIFS          | S3 sync / CIFS      | ECS service deploy     |

All three share the same Hermes CI/CD template library, Vault secrets, JFrog Artifactory, GitGuardian, SonarQube, and AWS OIDC auth.

---

(Source: direct codebase inspection of `~/work/cnf/app/source`)
