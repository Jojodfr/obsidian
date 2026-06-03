---
type: entity
title: "Kellymorphose"
category: product
status: developing
created: 2026-06-02
tags:
  - entity
  - product
  - hermes
  - webapp
  - vuejs
related:
  - "[[Hermes International]]"
  - "[[hb8-app-repo]]"
  - "[[klm-repo]]"
  - "[[Vue.js SPA Static Deployment]]"
---

# Kellymorphose

Navigation: [[entities/_index|Entities]] | [[index]]

Internal web application by Hermes International. A Vue.js single-page application for the Kellymorphose initiative, deployed as a static site on AWS S3.

## Technical Profile

- **Stack**: Vue.js (compiled SPA), AWS S3 static hosting
- **Auth**: Hermes SSO / OAuth2
- **CI/CD**: GitLab CI with Hermes template components
- **Asset management**: CIFS mount sync for large media files

## Relation to HB8

Kellymorphose and [[hb8-app-repo|HB8]] are sibling projects under the [[Hermes International]] GitLab organization. Both use the same CI/CD template library (`hermesintl/template-cicd`) and share the dual-deployment pattern (standard S3 + CIFS asset sync).

| | Kellymorphose | HB8 |
|---|---|---|
| Framework | Vue.js SPA | Nuxt.js 3 |
| Source in repo | Compiled only | Full source |
| Auth | Hermes SSO | JWT client-side |
| Complexity | Lower (static files) | Higher (build step) |
