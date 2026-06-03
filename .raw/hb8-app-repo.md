---
type: source
title: "HB8 App Repository"
source_type: codebase
path: ~/work/hb8/app
ingested: 2026-06-02
---

# HB8 App Repository

GitLab: hermesintl/hb8/projects/app/
Architecture: Two-repo monorepo (source + content-aws)

## source/
Nuxt.js (Vue.js) static web application. Title: "HB8 ou Les formes de la couleur"

### Stack
- Framework: Nuxt.js (static generation; deployed as SPA with `_nuxt/` chunks)
- Fonts: Custom web fonts (GaramondPremrPro family, MemphisLTStd family) via preloaded WOFF2
- Auth: JWT token decoding (`jwt-decode.js`) with login flow (`login.js`)
- Data: Excel file processing via `xlsx.full.min.js`
- Icons: Web manifest + apple-touch-icon configuration
- Private area: Separate `Private/` and `Public/` entry points

### Built Output
The `src/` directory contains a Nuxt build: minified JS chunks, lazy-loaded assets, fallback `200.html` for SPA routing. This is a production artifact, not source code. The actual source (Vue components, pages, store) is not present in this clone.

## content-aws/
AWS deployment scaffolding. GitLab CI configs (`gitlab-ci.yml`, `gitlab-ci-custom.yml`), CODEOWNERS, SAST/Security scanning config (`gitleaksignore`), SonarQube template (`sonar-project.properties.tpl`).

### CI/CD Pipeline
- GitLab CI as the deployment platform
- SonarQube for code quality analysis
- SAST (Static Application Security Testing) via GitLab built-in
- CODEOWNERS for merge request approval routing

## Project Owner
Hermes International (hermesintl org on GitLab)

## Observations
- This appears to be a deployment-only clone (build artifacts + infra), not the development repo
- The application name suggests an internal Hermes tool related to color/form management
- The two-folder architecture (source + content-aws) separates the built application from infrastructure-as-code
