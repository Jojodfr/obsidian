---
type: concept
title: "JWT Auth in Static Sites"
status: developing
created: 2026-06-02
tags:
  - concept
  - auth
  - jwt
  - security
  - static-site
related:
  - "[[Nuxt.js Static Deployment]]"
  - "[[Two-Repo Architecture]]"
  - "[[hb8-app-repo]]"
  - "[[index]]"
  - "[[concepts/_index|Concepts]]"
---

# JWT Auth in Static Sites

Navigation: [[index]] | [[concepts/_index|Concepts]] | [[hb8-app-repo]]

Authenticating users in a fully static site (no backend server) using JSON Web Tokens decoded and validated client-side.

## The Pattern

1. User authenticates via an external identity provider (or a lightweight auth endpoint)
2. Token is returned and stored (typically `localStorage` or `sessionStorage`)
3. `jwt-decode.js` parses the token payload client-side
4. The app reads claims (roles, permissions, expiry) and gates routes/content

## What Is Present in the [[hb8-app-repo]]

The built output includes:
- `jwt-decode.js` — decode token payload (no signature verification)
- `login.js` — custom login flow handler
- Separate `Private/` and `Public/` entry points

## Limitations

| Concern | Issue |
|---------|-------|
| No signature verification | `jwt-decode` only reads payload; anyone can forge a token |
| Token storage | `localStorage` is vulnerable to XSS |
| No token refresh | Static sites can't silently refresh without an API endpoint |
| Secret exposure | Any client-side validation logic is public |

## When It Is Acceptable

- Internal tools behind a VPN or corporate SSO
- Gated content that is not highly sensitive
- Short-lived tokens with frequent re-authentication prompts

## When It Is Not Acceptable

- Public-facing applications with sensitive data
- Financial or PII access
- Any scenario where token forgery would be catastrophic

## Better Alternatives

- Serverless auth functions (Netlify/Vercel edge functions, AWS Lambda@Edge)
- OAuth 2.0 with PKCE + redirect to a real backend
- Reverse proxy with token validation (Cloudflare Workers, API Gateway)

---

The [[hb8-app-repo]] uses this pattern for an internal Hermes tool, which is likely behind existing corporate network controls. For public or sensitive use, upgrade to one of the alternatives above.
