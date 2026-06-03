---
type: concept
title: "Nuxt.js Static Deployment"
status: mature
created: 2026-06-02
tags:
  - concept
  - nuxtjs
  - vue
  - static
  - deployment
related:
  - "[[Two-Repo Architecture]]"
  - "[[JWT Auth in Static Sites]]"
  - "[[hb8-app-repo]]"
  - "[[index]]"
  - "[[concepts/_index|Concepts]]"
---

# Nuxt.js Static Deployment

Navigation: [[index]] | [[concepts/_index|Concepts]] | [[hb8-app-repo]]

Deploying a Nuxt.js application as a fully static site (no server-side rendering) by generating HTML files at build time and serving them from a CDN or static host.

## How It Works

Nuxt in static mode (`nuxt generate`) pre-renders routes into HTML. For dynamic routes, it outputs a `200.html` fallback that the CDN serves for all unmatched paths, letting the client-side Vue router take over.

```
dist/ (or src/)
├── index.html           # Home page
├── 200.html             # SPA fallback for client-side routing
├── _nuxt/               # Minified JS chunks, lazy-loaded
│   ├── d01559a.js
│   ├── 0929b19.js
│   └── ...
├── favicon.ico
└── *.woff2              # Preloaded fonts
```

## Key Configuration

- `target: 'static'` in `nuxt.config.js`
- `generate.fallback: '200.html'` for SPA fallback on static hosts
- `<base href="/Public/">` when serving from a subpath

## Performance Techniques

| Technique | Effect |
|-----------|--------|
| `<link rel="preload">` for WOFF2 fonts | Eliminates font flash (FOUT) |
| Chunked JS via `_nuxt/` | Lazy loading, smaller initial payload |
| `hermes.webmanifest` | PWA installability |

## Auth in Static Nuxt

Without a backend, auth is client-side only. See [[JWT Auth in Static Sites]] for the pattern used in [[hb8-app-repo]].

## Hosting Options

- AWS S3 + CloudFront (used by [[hb8-app-repo]])
- Netlify, Vercel, GitHub Pages
- Any CDN that supports SPA fallback routing

---

See [[Two-Repo Architecture]] for how deployment configuration is separated from the build artifact.
