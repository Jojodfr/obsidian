---
type: concept
title: "Vue.js SPA Static Deployment"
created: 2026-06-02
updated: 2026-06-02
status: mature
tags:
  - concept
  - vuejs
  - deployment
  - static-site
  - aws
  - s3
related:
  - "[[Nuxt.js Static Deployment]]"
  - "[[klm-repo]]"
  - "[[Two-Repo Architecture]]"
---

# Vue.js SPA Static Deployment

Navigation: [[concepts/_index|Concepts]] | [[index]]

Pattern for deploying Vue.js single-page applications as static files to object storage (AWS S3, etc.). The compiled app runs entirely client-side with no server-side rendering.

## How It Works

1. **Build**: Vue CLI (`vue-cli-service build`) compiles source into hashed bundles
2. **Output**: `dist/` folder with `index.html`, JS chunks, CSS chunks, and assets
3. **Deploy**: Upload to S3 bucket configured for static website hosting
4. **Routing**: Client-side router (Vue Router) handles navigation; S3 serves `index.html` for all paths (SPA fallback)

## Bundle Structure

```
dist/
├── index.html              # SPA shell: <div id="app"></div>
├── js/
│   ├── app.xxxxxxxx.js     # Main app bundle
│   ├── chunk-vendors.js    # Third-party dependencies
│   └── home.xxxxxxxx.js    # Route-based lazy-loaded chunks
└── css/
    ├── app.xxxxxxxx.css    # Global styles
    └── home.xxxxxxxx.css   # Route-specific styles
```

## CI/CD Integration

Typical GitLab CI stages:

```yaml
stages:
  - build       # npm run build
  - test        # npm run test:unit
  - analysis    # SonarQube
  - deploy_chk  # aws s3 sync dist/ s3://bucket-chk/
  - deploy_prd  # aws s3 sync dist/ s3://bucket-prd/
```

## Comparison with Nuxt.js Static

| | Vue.js SPA | Nuxt.js Static |
|---|---|---|
| SEO | Poor (empty HTML shell) | Good (pre-rendered pages) |
| First paint | Slower (JS must load) | Faster (HTML ready) |
| Hosting | Simple S3 static | S3 with SPA fallback |
| Build output | Smaller (no HTML per page) | Larger (HTML per route) |
| Use case | Internal tools, dashboards | Public sites, content |

## Hermes Pattern

At Hermes International, Vue.js SPAs are deployed alongside Nuxt.js apps:
- **KLM** (Kellymorphose): Vue.js SPA, internal tool
- **HB8** (Haute Bijouterie 8): Nuxt.js static, public-facing

Both share the same S3 + CloudFront hosting stack and CI/CD template library.
