---
type: concept
title: "Two-Repo Architecture"
status: mature
created: 2026-06-02
tags:
  - concept
  - architecture
  - deployment
related:
  - "[[hb8-app-repo]]"
  - "[[Nuxt.js Static Deployment]]"
  - "[[index]]"
  - "[[concepts/_index|Concepts]]"
---

# Two-Repo Architecture

Navigation: [[index]] | [[concepts/_index|Concepts]] | [[hb8-app-repo]]

Splitting a single application into two repositories: one holds the built artifact (the application), the other holds infrastructure-as-code and deployment configuration.

## Pattern

```
app/
├── source/          # Built application (Nuxt output, dist, etc.)
└── content-aws/     # CI/CD, IaC, deployment config
```

## Why Use It

| Concern | Benefit |
|---------|---------|
| Separation of concerns | App team owns code; platform team owns deployment |
| Simpler permissions | Infra changes don't need app-repo access |
| Auditability | Deployment history is separate from code history |
| Reusability | Same infra repo can deploy multiple app versions |

## When It Fits

- Static sites (Nuxt, Next, Gatsby, Hugo) where the build is a set of files
- Organizations with separate platform and product teams
- Compliance requirements that want deployment configs versioned independently

## When It Does Not Fit

- Serverless backends where code and infra are tightly coupled
- Projects with frequent co-dependent app + deployment changes
- Small teams where the overhead of two PRs per change is wasteful

## Example

The [[hb8-app-repo]] uses this pattern: `source/` is the Nuxt.js static build; `content-aws/` is the GitLab CI pipeline that deploys to AWS. The build artifact is checked in and versioned separately from the pipeline that serves it.

---

See [[Nuxt.js Static Deployment]] for the app-side of this pattern.
