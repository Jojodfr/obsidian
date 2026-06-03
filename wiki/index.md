---
type: meta
title: "Wiki Index"
updated: 2026-06-03
tags:
  - meta
  - index
status: evergreen
related:
  - "[[overview]]"
  - "[[log]]"
  - "[[hot]]"
  - "[[dashboard]]"
  - "[[Wiki Map]]"
  - "[[concepts/_index]]"
  - "[[entities/_index]]"
  - "[[sources/_index]]"
  - "[[LLM Wiki Pattern]]"
  - "[[Hot Cache]]"
  - "[[Compounding Knowledge]]"
  - "[[Andrej Karpathy]]"
---

# Wiki Index

Last updated: 2026-06-03 | Total pages: 62 | Sources ingested: 9

Navigation: [[overview]] | [[log]] | [[hot]] | [[dashboard]] | [[Wiki Map]] | [[getting-started]]

---

## Concepts

- [[LLM Wiki Pattern]] — the pattern for building persistent, compounding knowledge bases using LLMs (status: mature)
- [[Hot Cache]] — ~500-word session context file, updated after every ingest and session (status: mature)
- [[Compounding Knowledge]] — why wiki knowledge grows more valuable over time, unlike RAG (status: mature)
- [[cherry-picks]] — prioritized feature backlog from ecosystem research; 13 features to add to claude-obsidian (status: current)
- [[SVG Diagram Style Guide]] — canonical visual style for all diagrams: Space Grotesk, #0A0A0A dark theme, #E07850 accent, full design tokens (status: evergreen)
- [[Pro Hub Challenge]] — community challenge pattern for building claude-seo/claude-blog extensions; first challenge produced 6 submissions, 5 integrated in v1.9.0 (status: evergreen)
- [[Semantic Topic Clustering]] — SERP-based keyword grouping replacing paid tools; hub-spoke architecture with interactive visualization (status: evergreen)
- [[Search Experience Optimization]] — "read SERPs backwards" methodology for page-type mismatch detection and persona scoring (status: evergreen)
- [[SEO Drift Monitoring]] — "git for SEO" baseline/diff/track with 17 comparison rules and SQLite persistence (status: evergreen)
- [[DragonScale Memory]] — memory-layer spec inspired by the Heighway dragon curve; fold operator, deterministic page addresses, semantic tiling, boundary-first autoresearch (status: shipped v0.4, all four mechanisms opt-in)
- [[Persistent Wiki Artifact]]: durable Markdown page as the LLM's memory object, distinct from ephemeral chat turns (status: developing)
- [[Source-First Synthesis]]: provenance discipline; raw sources stay immutable while the wiki layer is synthesized and cited (status: developing)
- [[Query-Time Retrieval]]: wiki query path synthesizes with citations; complementary to Obsidian's in-vault search (status: developing)
- [[Two-Repo Architecture]] — separating built app from infrastructure-as-code; used by [[hb8-app-repo]] (status: mature)
- [[Nuxt.js Static Deployment]] — static generation with SPA fallback; preloaded fonts and chunked JS (status: mature)
- [[JWT Auth in Static Sites]] — client-side JWT pattern without backend; limitations and alternatives (status: developing)
- [[Vue.js SPA Static Deployment]] — compiled Vue.js bundles to S3, client-side routing (status: mature)
- [[Symfony ECS Deployment]] — containerized Symfony to AWS ECS Fargate via Kaniko (status: mature)
- [[ClearID Integration Architecture]] — Hermes systems integration with Genetec ClearID; PHP scripts, OAuth2, Oracle HCM REST (status: current)
- [[Serverless SaaS Architecture]] — React PWA + Lambda monolith + DynamoDB single-table on AWS; distilled from [[Uplift Coach]] (status: developing)
- [[Ticketing System Architecture]] — Symfony API Platform + Angular SPA pattern for internal maintenance ticketing; from [[Shop Maintenance App]] (status: developing)

---

## Entities

- [[Andrej Karpathy]] — AI researcher, creator of the LLM Wiki pattern, former Tesla AI director (status: developing)
- [[Ar9av-obsidian-wiki]] — multi-agent compatible LLM Wiki plugin; delta tracking manifest (status: current)
- [[Nexus-claudesidian-mcp]] — native Obsidian plugin + MCP bridge; workspace memory, task management (status: current)
- [[ballred-obsidian-claude-pkm]] — goal cascade PKM; auto-commit hooks, /adopt command (status: current)
- [[rvk7895-llm-knowledge-bases]] — 3-depth query system, Marp slides, parallel deep research (status: current)
- [[kepano-obsidian-skills]] — official skills from Obsidian creator; defuddle, obsidian-bases (status: current)
- [[Claudian-YishenTu]] — native Obsidian plugin embedding Claude Code; plan mode, @mention (status: current)
- [[Claude SEO]] — Tier 4 Claude Code skill for SEO analysis; 23 skills, 17 agents, 30 scripts at v1.9.0 (status: evergreen)
- [[Hermes International]] — French luxury design house; owner of HB8 and Kellymorphose codebases (status: evergreen)
- [[Kellymorphose]] — Hermes internal web app (Vue.js SPA, AWS S3) (status: developing)
- [[Confie]] — Hermes internal web app (Symfony 7.2, AWS ECS Fargate) (status: developing)
- [[ClearID]] — Genetec physical access control platform; Hermes identity management hub (status: current)
- [[ClearID - MCH Interface]] — contract dates + photo sync Oracle HCM → ClearID (status: current)
- [[ClearID - SMI Interface]] — access rights sync SMI ↔ ClearID + user export (status: current)
- [[ClearID - IAM Interface]] — IAM identity mapping to ClearID (status: current)
- [[ClearID - Symfony API]] — Symfony 5.4 API layer for ClearID integrations (status: developing)
- [[8 Penthièvre]] — Hermes building website with interactive SVG floor plans (status: current)
- [[Uplift Coach]] — fitness coaching SaaS; React 19 PWA + Python Lambda + DynamoDB; production at app.upliftcoach.fr (status: current)
- [[Shop Maintenance App]] — Hermes internal maintenance ticketing system; Symfony 4.4 + Angular 17 + API Platform (status: current)

---

## Sources

- [[claude-obsidian-ecosystem-research]] — 2026-04-08 | web research across 16+ repos | 8 wiki pages created
- [[hb8-app-repo]] — 2026-06-02 | Nuxt.js static app for Hermes International | 5 wiki pages created
- [[klm-repo]] — 2026-06-02 | Vue.js SPA for Kellymorphose (Hermes) | 3 wiki pages created
- [[cnf-repo]] — 2026-06-02 | Symfony 7.2 app for Confie (Hermes) | 5 wiki pages created
- [[clearid-directory]] — 2026-06-03 | ClearID integration directory for Hermes | PHP scripts, Symfony API, MCH/SMI/IAM interfaces | 7 wiki pages created
- [[livret-penthievre-sources]] — 2026-06-03 | 8 Penthièvre building website | Vanilla HTML/CSS/JS, AWS S3, interactive SVG floor plans | 2 wiki pages created
- [[coach-app-repo]] — 2026-06-03 | Uplift Coach fitness SaaS | React 19 PWA, Python 3.13 Lambda, DynamoDB single-table, Stripe, Terraform, AWS | 3 wiki pages created
- [[shop-maintenance-app-repo]] — 2026-06-03 | Hermes Shop Maintenance App | Symfony 4.4 API, Angular 17 SPA, JWT auth, GitLab CI/CD, GCP | 4 wiki pages created

---

## Questions

- [[How does the LLM Wiki pattern work]] — how the pattern works and why it outperforms RAG at human scale (status: developing)

---

## Comparisons

- [[Wiki vs RAG]] — when to use a wiki knowledge base versus RAG; verdict: wiki wins at <1000 pages
- [[claude-obsidian-ecosystem]] — feature matrix of 16+ Claude+Obsidian projects; where claude-obsidian wins and gaps

---

## Decisions

- [[2026-04-14-community-cta-rollout]] - Skool community CTA footer added to 6 skill repos with per-tool frequency rules (status: active)
- [[2026-04-15-slides-and-release-session]] - Claude SEO v1.9.0 slides (15-slide HTML deck) + GitHub release v1.9.0 with PDF asset (status: complete)
- [[2026-04-15-release-report-session]] - Claude SEO v1.9.0 Release Report PDF: dark theme, 13 pages, WeasyPrint layout fixes, Challenge v2 added (status: complete)
- [[2026-04-14-claude-seo-v190-session]] - Claude SEO v1.9.0 Pro Hub Challenge integration: 5 submissions, 4 new skills, 4 review rounds, cybersecurity audit (status: complete)

---

## Sessions

- [[2026-06-02-hb8-ingestion-session]] — vault health check + HB8 repo ingestion + save pipeline demo (status: complete)

---

## Domains

<!-- Add domain entries here after scaffold -->
