---
source_type: repo
repo_url: https://gitlab.com/hermesintl/klm
cloned_at: 2026-06-02
path: ~/work/klm
---

# Kellymorphose (KLM) - Hermes International

## Project Identity

- **Name**: Kellymorphose
- **Code**: KLM
- **Company**: Hermes International (Hermes)
- **Platform**: GitLab (self-hosted or gitlab.com)
- **Type**: Frontend web application (Vue.js SPA)

## Repository Structure

```
klm/
└── app/
    └── source/
        ├── .gitlab-ci.yml          # Main CI/CD config
        ├── gitlab-ci-custom.yml    # Custom overrides (CIFS, Vault)
        ├── README.md               # GitLab template (empty)
        └── src/
            ├── index.html          # Entry point
            ├── Private/            # Authenticated area (Vue.js app)
            │   ├── index.html      # Vue SPA shell (<div id="app">)
            │   ├── css/            # 6 app bundles + 8 home bundles
            │   ├── js/             # 47 app bundles + 45 home bundles + 2 vendor chunks
            │   └── fonts/          # Custom typography
            └── Public/             # Public landing pages
                ├── index.html      # Login page (Hermes SSO)
                ├── demo_oauth2.html
                └── assets/
                    └── css/styles.css
```

## Technology Stack

| Layer | Technology | Evidence |
|-------|-----------|----------|
| Framework | Vue.js 2/3 | `chunk-vendors.js`, `app.*.js`, `home.*.js` route chunks, `<div id="app">` mount point |
| Build Tool | Vue CLI / webpack | Hashed chunk filenames, `chunk-vendors` naming, prefetch/preload links |
| Language | JavaScript (compiled) | Minified `.js` bundles, `.map` source maps |
| Styling | CSS (compiled) | Hashed `.css` bundles per route |
| Auth | Hermes SSO / OAuth2 | Login page with "Kellymorphose" branding, `demo_oauth2.html` |
| Hosting | AWS S3 static | `.gitlab-ci.yml` S3 deployment stages |
| CDN | CloudFront (implied) | S3 static site pattern typical for Hermes |
| CI/CD | GitLab CI | `.gitlab-ci.yml` with 17 stages |

## CI/CD Pipeline Analysis

### Stages (17 total)

```
init → build → sign → verify → manifest → test → analysis → push → publish → schedule → pre_deploy → deploy_chk → promote → release → deploy_prd → status → delete
```

### Key Configuration

- **Baseline component**: `hermesintl/template-cicd/baseline-component/baseline@~latest`
  - SonarQube at stage `analysis`
  - Language: `unset`, version `1.20.0`
- **S3 Push component**: `hermesintl/template-cicd/cicd-aws-components/s3-push-files@feat/jfrog-migration`
  - CHK: `s3://hsi-kellymorphose-chk-frontend/`
  - PRD: `s3://hsi-kellymorphose-prd-frontend/`
  - Upload subdir: `dist/`

### Environment Matrix

| Env | Enabled | Trigger | S3 Bucket |
|-----|---------|---------|-----------|
| DEV | false | - | - |
| TST | false | - | - |
| CHK | true | MR, non-tag branch | `hsi-kellymorphose-chk-frontend` |
| ACC | false | - | - |
| PRD | true | Git tag | `hsi-kellymorphose-prd-frontend` |

### Custom CI (gitlab-ci-custom.yml)

**Vault Integration**:
- Uses HashiCorp Vault for secrets
- `load_secret` command loads JSON secrets into CI variables
- `get-secrets-rct` (CHK) runs on MR, skips release commits
- `get-secrets-prd` runs on tags only

**Dual Deployment Path** (same pattern as HB8):

1. **Path A (standard)**: `s3-upload-content:chk/prd` — disabled (`when: never`)
2. **Path B (CIFS)**: `custom_s3-upload-content-cifs:chk/prd`
   - Mounts CIFS share: `//cifs-frsel/transfert_omnia/kellymorphose_cdn`
   - Syncs `src/` to S3
   - Syncs CIFS `img/` and `media/` to S3 `Private/img` and `Private/media`
   - Credentials: `svc_frsel_p_cisydep` / `SMB_PASS` / domain `ATLAS`

### Security

- **GitGuardian**: Full scan enabled (`GITGUARDIAN_FULL_SCAN: true`)
- **SonarQube**: Analysis stage via baseline component
- **Vault**: Secrets managed via HashiCorp Vault
- **Artifact registry**: JFrog Artifactory (`PUBLISH_REPOSITORY: artifactory`)

## Key Differences from HB8

| Aspect | HB8 | KLM |
|--------|-----|-----|
| Name | Haute Bijouterie 8 | Kellymorphose |
| Framework | Nuxt.js 3 | Vue.js SPA |
| Source in repo | Yes (full Nuxt project) | No (only compiled dist) |
| Secondary repo | content-aws (assets) | CIFS mount (assets) |
| Build complexity | High (Node.js, Docker, multi-stage) | Low (static files, no build in CI) |
| Auth | JWT client-side | Hermes SSO / OAuth2 |
| Environments | DEV, TST, CHK, ACC, PRD | CHK, PRD only |

## Observations

1. **Built artifacts only**: The repo contains compiled Vue.js bundles, not source code. The actual Vue source lives elsewhere (likely another repo or monorepo package).
2. **Static deployment**: No server-side rendering. Pure S3 static hosting with client-side routing.
3. **Asset management**: Large media files (images, videos) stored on CIFS and synced to S3 at deploy time — same hybrid pattern as HB8.
4. **Hermes standardization**: Uses the same `template-cicd` components as HB8, confirming Hermes has an internal CI/CD template library.
