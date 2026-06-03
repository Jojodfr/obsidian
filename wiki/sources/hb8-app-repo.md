---
type: source
title: "HB8 App Repository"
source_type: codebase
path: ~/work/hb8/app
status: developing
ingested: 2026-06-02
tags:
  - source
  - nuxtjs
  - hermes
related:
  - "[[Hermes International]]"
  - "[[Nuxt.js Static Deployment]]"
  - "[[Two-Repo Architecture]]"
  - "[[JWT Auth in Static Sites]]"
  - "[[index]]"
  - "[[log]]"
---

# HB8 App Repository

Navigation: [[index]] | [[sources/_index|Sources]] | [[Hermes International]]

GitLab: `hermesintl/hb8/projects/app/`

---

## What It Is

A Nuxt.js static web application for [[Hermes International]]. The app title is **"HB8 ou Les formes de la couleur"** (The Forms of Color), suggesting an internal tool for color and form management in the luxury fashion pipeline.

The repository uses a [[Two-Repo Architecture]]: `source/` contains the built Nuxt application; `content-aws/` contains infrastructure-as-code and CI/CD configuration.

---

## source/ — Built Application

This is a production artifact, not the development source. The Vue/Nuxt source code is in a separate repository or branch. What is present: a static Nuxt SPA with preloaded resources.

### Stack

| Component | Technology                                             |
| --------- | ------------------------------------------------------ |
| Framework | Nuxt.js (Vue.js) — static site generation              |
| Auth      | JWT client-side token decoding (`jwt-decode.js`)       |
| Login     | Custom login flow (`login.js`)                         |
| Data      | Excel file processing (`xlsx.full.min.js`)             |
| Fonts     | GaramondPremrPro (4 weights), MemphisLTStd (2 weights) |
| Routing   | SPA fallback via `200.html`                            |

### Architecture

Two entry points: `Private/` (authenticated) and `Public/` (open). The Public build uses minified JS chunks in `_nuxt/` for lazy loading. A `hermes.webmanifest` and `robots.txt` are included.

The Nuxt build target is a fully static deployment (no SSR), served from AWS S3/CloudFront via the [[Two-Repo Architecture]] pipeline.

---

## content-aws/ — Infrastructure

| File | Purpose |
|------|---------|
| `gitlab-ci.yml` | Primary CI/CD pipeline |
| `gitlab-ci-custom.yml` | Custom deployment stage overrides |
| `CODEOWNERS` | Merge request approval routing |
| `.gitleaksignore` | Secret scanning exclusions |
| `sonar-project.properties.tpl` | Code quality analysis config template |

---

## CI/CD Pipeline (Complete)

### All 20 Stages

```
init → build → sign → verify → manifest → test → analysis → push → publish → schedule → pre_deploy → deploy_dev → deploy_tst → deploy_chk → deploy_acc → promote → release → deploy_prd → status → delete
```

### Environment Matrix

| Env | Enabled | Trigger | AWS Account | S3 Bucket | CIFS Path |
|-----|---------|---------|-------------|-----------|-----------|
| DEV | true | Manual | - | - | - |
| TST | true | Manual | - | - | - |
| CHK | true | Merge Request | 774023531865 | `s3://hsi-hautebijouterie8-chk-frontend/` | `//cifs-frsel/TST-HLS` |
| ACC | true | Manual | - | - | - |
| PRD | true | Git tag | 359100918811 | `s3://hsi-hautebijouterie8-prd-frontend/` | `//cifs-frsel/PRD-HLS` |

### CI Components (Hermes Template Library)

The pipeline is composed from reusable GitLab CI components. **Two different `.gitlab-ci.yml` files** exist — one per repo:

**`source/.gitlab-ci.yml`** (application artifact):
- **Baseline component** (`baseline-component/baseline@~latest`)
  - Provides core stages: init, build, test, analysis, manifest, sign, verify, push, publish, schedule
  - Inputs: sonarqube_stage=`analysis`, language=`unset`, language_version=`1.20.0`, tool=`unset`
- **S3 Push component** (`s3-push-files@feat/jfrog-migration`) ×2
  - CHK: account `774023531865`, bucket `hsi-hautebijouterie8-chk-frontend/`, **upload_subdir=`dist/`**
  - PRD: account `359100918811`, bucket `hsi-hautebijouterie8-prd-frontend/`, **upload_subdir=`dist/`**
- **Custom**: `gitlab-ci-custom.yml` (local include)

**`content-aws/.gitlab-ci.yml`** (infrastructure + generic files):
- **GitGuardian component** (`gitguardian-component/gitguardian@1.1.6`)
  - Secret scanning at init stage
- **JFrog setup** (remote: `releases.jfrog.io/artifactory/jfrog-cli/gitlab/v2/.setup-jfrog-unix.yml`)
  - CLI tool for artifact management
- **S3 Push component** (`s3-push-files@feat/jfrog-migration`) ×2
  - CHK & PRD: same accounts/buckets as source
  - **No upload_subdir** (generic file upload)
- **Custom**: `gitlab-ci-custom.yml` (local include)

> Key difference: `source/` uses `upload_subdir: "dist/"` because it builds into a `dist/` folder. `content-aws/` has no `upload_subdir` because its files are generic S3 content.

### Key Variables

```yaml
PUBLISH_REPOSITORY: artifactory
GITGUARDIAN_FULL_SCAN: "true"
DOCKER_TLS_CERTDIR: "/certs"
GIT_SSL_NO_VERIFY: "true"
DEV_ENABLED: "true"
TST_ENABLED: "true"
CHK_ENABLED: "true"
ACC_ENABLED: "true"
```

### Deployment Paths

**Path A (Standard — via S3 push component)**:
1. GitLab CI builds Nuxt.js artifact
2. JFrog Artifactory stores the artifact
3. Reusable S3 push component uploads to target bucket
4. AWS OIDC authentication (no hardcoded credentials)
5. `s3 sync` completes the deployment

**Path B (Custom CIFS — via gitlab-ci-custom.yml)**:
1. HashiCorp Vault retrieves secrets via `load_secret`
2. Custom Docker runner mounts CIFS share
3. SMB connection to `//cifs-frsel/TST-HLS` (CHK) or `//cifs-frsel/PRD-HLS` (PRD)
4. AWS OIDC authentication
5. `aws s3 sync /cifs/... $S3_BUCKET` syncs media assets

### Security Stack

| Tool | Stage | Purpose |
|------|-------|---------|
| GitGuardian | init | Full repository secret scanning |
| SonarQube | analysis | Code quality baseline analysis |
| Vault | init | Secret retrieval via `load_secret` |
| JFrog | push/publish | Artifact storage and registry |
| AWS OIDC | deploy | Credential-less AWS authentication |

---

## Key Observations

- Built artifact repo, not development source (Vue components absent)
- JWT auth is client-side only with a custom login module (no backend endpoints visible)
- Excel processing suggests bulk data import or color specification exchange
- Custom font loading via `preload` hints for perceived performance
- The "HB8" naming convention suggests a series or versioned line of internal tools
- Two-repo architecture separates app code from infrastructure-as-code
- CIFS mount path differs per environment (TST-HLS vs PRD-HLS)

---

## CI/CD Pipeline Diagram

Static version:

![[hb8-cicd-pipeline.svg|697]]

> **Interactive version**: Open `wiki/canvases/hb8-cicd-pipeline.canvas` in Obsidian for a fully interactive diagram with clickable nodes linked to wiki pages.

Full pipeline architecture in the SVG Diagram Style Guide. Key insight: **two deployment paths** exist side by side:
- **Path A (standard):** JFrog Artifactory → reusable S3 push component → AWS
- **Path B (custom CIFS):** Vault secrets → CIFS mount (`//cifs-frsel/`) → `aws s3 sync` → AWS

---

(Source: direct codebase inspection of `~/work/hb8/app`)
