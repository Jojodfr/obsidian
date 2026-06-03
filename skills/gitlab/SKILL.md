---
name: gitlab
description: "Analyze GitLab repositories, parse .gitlab-ci.yml pipelines, ingest repo structure and DevOps findings into the wiki. Triggers on: gitlab, analyze repo, parse pipeline, ci/cd analysis, gitlab ingest, repo structure."
allowed-tools: Read Write Edit Glob Grep Bash PowerShell
---

# gitlab: GitLab Repository Analysis

Analyze GitLab repositories end-to-end: stack detection, CI/CD pipeline tracing, architecture mapping, and DevOps concept extraction. All findings are filed into the wiki via `wiki-ingest`.

---

## When to Use

| User says | Action |
|-----------|--------|
| "analyze this GitLab repo" | Full repo analysis |
| "parse the CI/CD pipeline" | `.gitlab-ci.yml` deep dive |
| "what's the stack?" | Technology detection |
| "gitlab ingest [path]" | Ingest repo summary into wiki |
| "trace this pipeline stage" | Stage-to-job-to-artifact mapping |

---

## Prerequisites

The repo must be cloned locally. This skill reads files, not the GitLab API.

```
~/work/<project>/app/
  .gitlab-ci.yml       # CI/CD definition
  package.json         # Node.js deps
  composer.json        # PHP deps
  requirements.txt     # Python deps
  Dockerfile           # Container build
  docker-compose.yml   # Local orchestration
  pom.xml / build.gradle # Java
  go.mod               # Go
  Cargo.toml           # Rust
```

---

## Full Repo Analysis

### Step 1: Structure Map

Read the top-level directory tree (depth 2-3). Identify:
- Language/runtime (from package manifests)
- Framework (from deps and directory conventions)
- Monorepo vs. polyrepo (single app vs. multiple services)
- Infrastructure as Code (Terraform, CloudFormation, Helm charts)

### Step 2: Stack Detection Table

Create a summary table:

| Layer | Technology | Evidence |
|-------|------------|----------|
| Frontend | Nuxt.js 3 | `package.json` deps, `nuxt.config.ts` |
| Backend | Node.js 20 | `Dockerfile` FROM line, `.nvmrc` |
| Database | PostgreSQL | `docker-compose.yml` service |
| Auth | JWT (client-side) | `jwt-decode.js` in source |
| CI/CD | GitLab CI | `.gitlab-ci.yml` |
| Deploy | AWS S3 + CloudFront | CI stage `deploy-front` |
| Registry | JFrog Artifactory | `jfrog rt upload` in CI |

### Step 3: CI/CD Pipeline Trace

Read `.gitlab-ci.yml` completely. Extract:

**Stages**: List in execution order.
**Jobs**: Per stage, list jobs with:
- `image:` (runner Docker image)
- `script:` commands (summarize, don't dump)
- `artifacts:` paths and expiry
- `dependencies:` / `needs:` (DAG edges)
- `rules:` / `only:` / `except:` (branch/merge-request triggers)
- `variables:` (environment-specific)

**Deployment paths**: Trace artifacts from build stage → registry → deploy target.

**Security gates**: Identify scanning stages (SAST, secret detection, SonarQube).

### Step 4: Architecture Narrative

Write 3-5 paragraphs synthesizing:
1. What the application does (domain)
2. How it's structured (monolith vs. microservices)
3. How code flows from commit to production
4. Key external dependencies (APIs, vaults, artifact stores)
5. Notable DevOps patterns (blue-green, canary, feature flags)

---

## Pipeline Diagram Generation

Generate an SVG diagram following the vault's **SVG Diagram Style Guide**:

```yaml
style:
  font: Space Grotesk, sans-serif
  background: "#0A0A0A"
  accent: "#E07850"
  card_bg: "#141414"
  text: "#EAEAEA"
  secondary_text: "#A0A0A0"
  border: "#2A2A2A"
```

Layout:
- Horizontal swimlanes per **stage**
- Vertical job cards within each stage
- Arrows show `dependencies:` / `needs:`
- Color-code by category: build (blue), test (green), security (orange), deploy (red), infrastructure (purple)
- Annotate dual paths (e.g., Path A: JFrog → S3 component vs. Path B: CIFS → `aws s3 sync`)

Save to `_attachments/images/<repo-name>-cicd-pipeline.svg`.
Embed in the source wiki page with `![[<repo-name>-cicd-pipeline.svg]]`.

---

## Ingestion Pipeline

After analysis, file everything into the wiki via `wiki-ingest` conventions:

1. **Source page**: `wiki/sources/<repo-name>.md`
   - Full stack table
   - Architecture narrative
   - Pipeline summary
   - Embedded diagram

2. **Entity pages** (if new):
   - `wiki/entities/GitLab.md` (if not exists)
   - `wiki/entities/<Product-Name>.md` (the application itself)
   - `wiki/entities/JFrog-Artifactory.md`, `wiki/entities/SonarQube.md`, etc. (tools detected)

3. **Concept pages** (if new):
   - `wiki/concepts/GitLab-CI-CD.md` — reusable reference for `.gitlab-ci.yml` structure
   - `wiki/concepts/<Pattern-Name>.md` — specific patterns observed (e.g., Dual-Deployment-Path)

4. **Domain update**: `wiki/domains/devops/_index.md` or `wiki/domains/software-engineering/_index.md`

5. **Meta session note**: `wiki/meta/YYYY-MM-DD-<repo>-analysis.md`

6. **Index/log/hot**: Update per `wiki-ingest` rules.

---

## GitLab-Specific Concepts

When creating concept pages for GitLab CI/CD:

### `.gitlab-ci.yml` Structure
```yaml
# Top-level keys
stages: [build, test, deploy]
variables:
  GLOBAL_VAR: value

# Job definitions
job_name:
  stage: build
  image: node:20
  script:
    - npm ci
    - npm run build
  artifacts:
    paths: [dist/]
    expire_in: 1 week
  only: [main, merge_requests]
```

### Key GitLab CI Concepts
- **Stages**: Sequential phases. All jobs in a stage must complete before next stage starts.
- **Jobs**: Units of work. Jobs in same stage run in parallel.
- **Needs**: DAG dependency. A job can `needs:` another job from an earlier stage to start earlier.
- **Artifacts**: Files passed between jobs. Expire and are deleted after expiry.
- **Cache**: Persisted between pipelines (e.g., `node_modules`).
- **Rules**: Conditional job execution based on branch, MR, variables, changes.
- **Include**: Modularize CI config by including other `.yml` files.
- **Components**: Reusable CI/CD components (v16.0+).

---

## Windows Compatibility

This skill runs on both Unix and Windows. When using PowerShell:
- Use `Get-Content` instead of `cat`
- Use `Join-Path` with 2-arg nesting (PS 5.1 limitation)
- Avoid em-dashes (U+2014) in strings
- Use `Test-Path` instead of `[ -f ]`

For `wiki-lock`, call `scripts/wiki-lock.ps1` on Windows instead of `scripts/wiki-lock.sh`.

---

## What Not to Do

- Do not assume the repo is on gitlab.com; it may be self-hosted.
- Do not dump the entire `.gitlab-ci.yml` into the wiki; synthesize and summarize.
- Do not create entity pages for standard libraries or common tools (e.g., `npm`, `Docker`) unless they have project-specific configuration worth documenting.
- Do not skip the diagram if the pipeline has 3+ stages; visual context compounds.

---

## How to think (10-principle mapping)

| # | Principle | Application here |
|---|-----------|-------------------|
| 1 | OBSERVE (ext) | Read the full repo tree and `.gitlab-ci.yml` before drawing conclusions. |
| 2 | OBSERVE (int) | Am I confusing GitLab CI with GitHub Actions? They share concepts but differ in syntax. |
| 3 | LISTEN | What does the user want to know? Stack? Pipeline? Security posture? Deployment path? |
| 4 | THINK | Which stages are critical path? Which are parallelizable? Where are the failure points? |
| 5 | CONNECT (lat) | How does this pipeline compare to others in the wiki? Same tools? Different patterns? |
| 6 | CONNECT (sys) | CI/CD feeds into deployment feeds into monitoring. Trace the full chain. |
| 7 | FEEL | A pipeline diagram should be readable at a glance. Dense is worse than simplified. |
| 8 | ACCEPT | Some repos have messy CI. Document the mess without sanitizing it. |
| 9 | CREATE | Stack table + architecture narrative + pipeline diagram + concept pages. |
| 10 | GROW | Each GitLab repo ingested improves the DevOps domain in the wiki. |
