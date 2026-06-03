---
type: session
title: "HB8 Repo Ingestion Session"
created: 2026-06-02
updated: 2026-06-02
status: complete
tags:
  - session
  - nuxtjs
  - hermes
  - wiki-pattern
related:
  - "[[hb8-app-repo]]"
  - "[[Hermes International]]"
  - "[[Two-Repo Architecture]]"
  - "[[Nuxt.js Static Deployment]]"
  - "[[JWT Auth in Static Sites]]"
  - "[[index]]"
  - "[[log]]"
  - "[[hot]]"
---

# HB8 Repo Ingestion Session

Navigation: [[index]] | [[log]] | [[hot]] | [[hb8-app-repo]]

Date: 2026-06-02
Scope: vault health check + repo ingestion + save pipeline demonstration

---

## What Happened

This session walked through the full claude-obsidian workflow: vault setup verification, ingestion of a real work repository, and filing of this conversation via `/save`.

### 1. Vault Health Check (`/wiki`)

The vault was already heavily populated (45+ wiki pages). Key findings:
- Hot cache was stale (last updated 2026-05-17, 16 days ago)
- Index undercounted pages (claimed 34, actual 45+)
- Excalidraw `main.js` was missing (downloaded, 8.2 MB)
- All config files (graph.json, app.json, appearance.json) were correct
- DragonScale Memory fully shipped (all 4 mechanisms)

Actions taken: downloaded Excalidraw main.js, noted stale cache for refresh during ingestion.

### 2. Repo Ingestion (`ingest ~/work/hb8/app`)

The user asked to ingest `~/work/hb8/app`. Initial `claude plugin install` commands failed (no `claude` CLI in PATH). Proceeded with direct vault ingestion instead.

**Repo structure:**
- `source/` — Nuxt.js built static SPA (not dev source)
- `content-aws/` — GitLab CI/CD + AWS deployment config

**Key findings:**
- App: "HB8 ou Les formes de la couleur" (Hermes International internal tool)
- Stack: Nuxt.js (Vue), JWT client-side auth, Excel processing (`xlsx.full.min.js`), custom Garamond/Memphis web fonts
- Architecture: [[Two-Repo Architecture]] (built app separate from infra config)
- Deployment: GitLab CI/CD to AWS, SonarQube + SAST enabled

**Pages created:**
| Page | Type | Folder |
|------|------|--------|
| [[hb8-app-repo]] | source | `wiki/sources/` |
| [[Hermes International]] | entity | `wiki/entities/` |
| [[Two-Repo Architecture]] | concept | `wiki/concepts/` |
| [[Nuxt.js Static Deployment]] | concept | `wiki/concepts/` |
| [[JWT Auth in Static Sites]] | concept | `wiki/concepts/` |

**Raw source filed:** `.raw/hb8-app-repo.md`

### 3. Save Pipeline Demonstration (`/save`)

User asked "how does /save work?" then triggered `/save` on this conversation. This note is the result.

The save pipeline:
1. Analyze conversation → determine type (`session` for full multi-topic record)
2. Write note to `wiki/meta/<date>-<topic>-session.md`
3. Update `wiki/index.md` (add session entry to catalog)
4. Update `wiki/log.md` (prepend entry)
5. Update `wiki/hot.md` (refresh cache with session context)

---

## Technical Notes

### Built-Output Ingestion

The HB8 repo contained built artifacts (minified JS chunks, `200.html`, `index.html`) rather than Vue/Nuxt source code. This is common in deployment-only repositories where CI/CD compiles source from a separate repo and commits the output here. Ingestion of built output is limited: you can infer tech stack (Nuxt.js from `__NUXT__` global and `_nuxt/` chunks) but not read component logic or business logic.

### Client-Side JWT Limitations

The repo used `jwt-decode.js` (payload parsing only, no signature verification). This is acceptable for internal tools behind VPN/corporate network but would be a security BLOCKER for public-facing apps. Noted in [[JWT Auth in Static Sites]] with alternatives (serverless edge functions, OAuth 2.0 + PKCE, reverse proxy validation).

### Two-Repo Architecture

This pattern scales well for static sites where the build artifact is a set of files. The [[hb8-app-repo]] uses it correctly: `source/` is the artifact; `content-aws/` is the pipeline. Tradeoff: two PRs per change that touches both app and deployment. Ideal when platform and product teams are separate.

---

## Pages Modified in This Session

| File | Action |
|------|--------|
| `.raw/hb8-app-repo.md` | created |
| `wiki/sources/hb8-app-repo.md` | created |
| `wiki/entities/Hermes International.md` | created |
| `wiki/concepts/Two-Repo Architecture.md` | created |
| `wiki/concepts/Nuxt.js Static Deployment.md` | created |
| `wiki/concepts/JWT Auth in Static Sites.md` | created |
| `wiki/meta/2026-06-02-hb8-ingestion-session.md` | created (this note) |
| `wiki/index.md` | updated (counts, new entries in 3 sections + new Sessions section) |
| `wiki/log.md` | updated (prepend ingest + save entries) |
| `wiki/hot.md` | rewritten (complete refresh) |
| `wiki/sources/_index.md` | updated (added hb8 entry) |
| `wiki/entities/_index.md` | updated (added Hermes entry) |
| `wiki/concepts/_index.md` | updated (added 3 concepts) |

---

### 4. CI/CD Pipeline Deep Dive (post-ingest)

User asked for an Excalidraw-style diagram of the CI/CD pipeline. I created an SVG architecture diagram instead (the project's canonical format per the SVG Diagram Style Guide). Reading the actual `.gitlab-ci.yml`, `gitlab-ci-custom.yml`, and `source/.gitlab-ci.yml` revealed much more than the initial scan:

**Name correction:** The S3 buckets (`hsi-hautebijouterie8-*`) reveal this is **"Haute Bijouterie 8"** — a **Hermes High Jewelry** production system, not merely a color/form tool.

**Two deployment paths (not one):**

| Path | Repo | Mechanism |
|------|------|-----------|
| **A: Standard** | `source/` | Nuxt `dist/` → JFrog Artifactory → reusable S3 component → AWS |
| **B: Custom CIFS** | `content-aws/` | Vault secrets → CIFS mount (`//cifs-frsel/TST-HLS` or `PRD-HLS`) → `aws s3 sync` → AWS |

Path B is unique: it mounts a Windows network share with SMB credentials, then syncs `/cifs/$MOUNT_FOLDER/datas_hb8/data` to S3. This implies content is authored on a shared drive and deployed via sync rather than built in CI.

**Security stack:**
- GitGuardian (FULL_SCAN: true) for secret scanning
- SonarQube baseline component for code quality
- HashiCorp Vault for per-environment secret retrieval
- AWS OIDC (no hardcoded credentials — `AWS_OIDC_TOKEN` used)
- JFrog Artifactory for artifact storage
- `.gitleaksignore` for secret scanning exclusions

**Notable:** The S3 push component is on branch `feat/jfrog-migration`, suggesting an active migration from another artifact store. The pipeline has 20 stages including sign, verify, promote — consistent with regulated luxury-goods release processes.

**Diagram created:** `_attachments/images/hb8-cicd-pipeline.svg`

---

## Pages Modified in This Session

| File | Action |
|------|--------|
| `.raw/hb8-app-repo.md` | created |
| `wiki/sources/hb8-app-repo.md` | created |
| `wiki/entities/Hermes International.md` | created |
| `wiki/concepts/Two-Repo Architecture.md` | created |
| `wiki/concepts/Nuxt.js Static Deployment.md` | created |
| `wiki/concepts/JWT Auth in Static Sites.md` | created |
| `wiki/meta/2026-06-02-hb8-ingestion-session.md` | created (this note) |
| `_attachments/images/hb8-cicd-pipeline.svg` | created (CI/CD diagram) |
| `wiki/index.md` | updated (counts, new entries in 3 sections + new Sessions section) |
| `wiki/log.md` | updated (prepend ingest + save entries) |
| `wiki/hot.md` | rewritten (complete refresh) |
| `wiki/sources/_index.md` | updated (added hb8 entry) |
| `wiki/entities/_index.md` | updated (added Hermes entry) |
| `wiki/concepts/_index.md` | updated (added 3 concepts) |

---

## Lessons

1. Ingest built-output repos for architecture/tech-stack extraction, not for business-logic analysis. Source code repos yield richer concept extraction.
2. The vault's stale hot cache is a common pattern when resuming after a break. Ingestion is the perfect trigger to refresh it.
3. User onboarding via `/wiki` should differentiate between fresh vault scaffolds and existing-vault health checks. This vault was the latter.
4. The `claude` CLI command not being in PATH doesn't block any functionality when working inside the vault directly.
5. CI/CD deep reads often reveal the true domain of a project faster than application code. S3 bucket names, component inputs, and environment variables are high-signal identifiers.
