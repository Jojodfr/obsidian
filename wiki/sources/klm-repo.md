---
type: source
title: "Kellymorphose (KLM) Repository"
created: 2026-06-02
updated: 2026-06-02
source: ".raw/klm-repo.md"
status: mature
tags:
  - source
  - repo
  - gitlab
  - hermes
  - vuejs
  - cicd
related:
  - "[[hb8-app-repo]]"
  - "[[Hermes International]]"
  - "[[Kellymorphose]]"
  - "[[Vue.js SPA Static Deployment]]"
  - "[[Two-Repo Architecture]]"
---

# Kellymorphose (KLM) Repository

Navigation: [[sources/_index|Sources]] | [[index]] | [[hb8-app-repo]]

Hermes International GitLab repository for the **Kellymorphose** web application. Vue.js SPA deployed to AWS S3 via GitLab CI/CD with CIFS asset sync.

---

## Quick Facts

| Attribute | Value |
|-----------|-------|
| **Project** | Kellymorphose (KLM) |
| **Company** | Hermes International |
| **Type** | Frontend web application |
| **Framework** | Vue.js (compiled SPA) |
| **Auth** | Hermes SSO / OAuth2 |
| **Hosting** | AWS S3 static |
| **CI/CD** | GitLab CI (17 stages) |
| **Repo** | `gitlab.com/hermesintl/klm` |
| **Local path** | `~/work/klm` |

---

## Technology Stack

| Layer | Technology | Evidence |
|-------|-----------|----------|
| Framework | Vue.js 2/3 | `chunk-vendors.js`, route-based chunks (`home.*.js`), `<div id="app">` mount point |
| Build Tool | Vue CLI / webpack | Hashed chunk filenames, prefetch/preload links |
| Language | JavaScript (compiled) | Minified `.js` bundles with source maps |
| Styling | CSS (compiled) | Hashed `.css` bundles per route |
| Auth | Hermes SSO | Login page with Kellymorphose branding |
| Hosting | AWS S3 static | `.gitlab-ci.yml` S3 deployment stages |
| CI/CD | GitLab CI | 17-stage pipeline with Hermes template components |

---

## Repository Structure

```
klm/
└── app/
    └── source/
        ├── .gitlab-ci.yml          # Main CI/CD config
        ├── gitlab-ci-custom.yml    # Custom overrides (CIFS, Vault)
        └── src/
            ├── index.html
            ├── Private/            # Vue.js SPA (authenticated)
            │   ├── index.html      # Vue shell
            │   ├── css/            # 6 app + 8 home CSS bundles
            │   ├── js/             # 47 app + 45 home JS bundles + vendors
            │   └── fonts/
            └── Public/             # Public landing pages
                ├── index.html      # Login page
                └── assets/css/
```

**Key observation**: The repo contains only **compiled/dist output**, not Vue source code. The actual source lives elsewhere.

---

## CI/CD Pipeline (Complete)

### All 17 Stages

```
init → build → sign → verify → manifest → test → analysis → push → publish → schedule → pre_deploy → deploy_chk → promote → release → deploy_prd → status → delete
```

### Environment Matrix

| Env | Enabled | Trigger | AWS Account | S3 Bucket | CIFS Path |
|-----|---------|---------|-------------|-----------|-----------|
| DEV | false | - | - | - | - |
| TST | false | - | - | - | - |
| CHK | true | Merge Request | 023381192909 | `s3://hsi-kellymorphose-chk-frontend/` | `//cifs-frsel/transfert_omnia/kellymorphose_cdn` |
| ACC | false | - | - | - | - |
| PRD | true | Git tag | 737247133998 | `s3://hsi-kellymorphose-prd-frontend/` | `//cifs-frsel/transfert_omnia/kellymorphose_cdn` |

### CI Components (Hermes Template Library)

The pipeline is composed from reusable GitLab CI components defined in the `hermesintl/template-cicd` library.

**`.gitlab-ci.yml` (main config)**:
- **Baseline component** (`baseline-component/baseline@~latest`)
  - Provides core stages: init, build, sign, verify, manifest, test, analysis, push, publish, schedule
  - Inputs: sonarqube_stage=`analysis`, language=`unset`, language_version=`1.20.0`, tool=`unset`
- **S3 Push component** (`s3-push-files@feat/jfrog-migration`) ×2
  - CHK: account `023381192909`, bucket `hsi-kellymorphose-chk-frontend/`, upload_subdir=`dist/`
  - PRD: account `737247133998`, bucket `hsi-kellymorphose-prd-frontend/`, upload_subdir=`dist/`
  - Artifact type: `generic`
- **Custom**: `gitlab-ci-custom.yml` (local include)

**`gitlab-ci-custom.yml` (custom overrides)**:
- **Vault component** (`vault-component/vault@~latest`)
  - Retrieves secrets via `load_secret` command
  - Secrets injected as CI variables via `dotenv` report
- **S3 Push component** (`s3-push-files@feat/jfrog-migration`) ×2
  - Same CHK/PRD targets but with `source_directory: "src/"` and custom runner image
  - Used by custom CIFS jobs

### Key Variables

```yaml
PUBLISH_REPOSITORY: artifactory
GITGUARDIAN_FULL_SCAN: "true"
DOCKER_TLS_CERTDIR: "/certs"
GIT_SSL_NO_VERIFY: "true"
DEV_ENABLED: "false"
TST_ENABLED: "false"
CHK_ENABLED: "true"
ACC_ENABLED: "false"
S3_DEPLOY_IMAGE: $CI_REGISTRY/itops/docker-aws-cli-custom:latest
SMB_USER: svc_frsel_p_cisydep
SMB_PATH: //cifs-frsel/transfert_omnia/kellymorphose_cdn
```

### Custom Jobs (gitlab-ci-custom.yml)

**get-secrets-rct** (CHK):
- Extends: `.get-secrets-template`
- Environment: `chk`
- Rules: MR event, non-tag commits
- Excludes: release commits (`chore(release)`), tags

**get-secrets-prd** (PRD):
- Extends: `.get-secrets-template`
- Environment: `prd`
- Rules: Git tags only

**custom_s3-upload-content-cifs:chk**:
- Runner image: `docker-aws-cli-custom:latest`
- Script:
  1. `aws s3 sync src/ $S3_BUCKET --delete`
  2. Mount CIFS with credentials from Vault
  3. `aws s3 sync /cifs/klm/img $S3_BUCKET/Private/img --delete`
  4. `aws s3 sync /cifs/klm/media $S3_BUCKET/Private/media --delete`
- Tags: `s3`, `cifs`

**custom_s3-upload-content-cifs:prd**:
- Same as CHK but triggered by Git tags
- Manual approval required (`when: manual`)

### Deployment Paths

**Path A (Standard — disabled)**:
- `s3-upload-content:chk` and `s3-upload-content:prd` are disabled (`rules: when: never`)
- S3 push component handles deployment via included template

**Path B (Custom CIFS — active)**:
1. Vault retrieves `SMB_PASS` secret
2. Custom runner mounts CIFS: `mount -t cifs $SMB_PATH /cifs/klm`
3. Credentials file: `/cifs/.cifs` with username, password, domain=ATLAS
4. Sync `src/` to S3 bucket
5. Sync CIFS `img/` and `media/` to S3 `Private/img` and `Private/media`

### Security Stack

| Tool | Stage | Purpose |
|------|-------|---------|
| GitGuardian | init | Full repository secret scanning |
| SonarQube | analysis | Code quality baseline analysis |
| Vault | init | Secret retrieval via `load_secret` |
| JFrog | push/publish | Artifact storage (`PUBLISH_REPOSITORY: artifactory`) |
| AWS OIDC | deploy | Credential-less AWS authentication |

### Runner Tags

| Job | Tags |
|-----|------|
| Custom CIFS deploy | `s3`, `cifs` |
| Standard S3 deploy | (default runners) |

---

## CI/CD Diagram

Static version:

![[klm-cicd-pipeline.svg]]

> **Interactive version**: Open `wiki/canvases/klm-cicd-pipeline.canvas` in Obsidian for a fully interactive diagram with clickable nodes linked to wiki pages.

---

## Comparison with HB8

| Aspect         | HB8                     | KLM                 |
| -------------- | ----------------------- | ------------------- |
| Name           | Haute Bijouterie 8      | Kellymorphose       |
| Framework      | Nuxt.js 3               | Vue.js SPA          |
| Source in repo | Yes (full source)       | No (compiled only)  |
| Asset source   | content-aws repo        | CIFS mount          |
| Build in CI    | Yes (Node.js, Docker)   | No (static files)   |
| Auth           | JWT client-side         | Hermes SSO / OAuth2 |
| Environments   | 5 (DEV/TST/CHK/ACC/PRD) | 2 (CHK/PRD)         |

Both share the same Hermes CI/CD template library, dual-deployment pattern, and AWS S3 hosting.
