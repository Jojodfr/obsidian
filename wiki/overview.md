---
type: overview
title: "Wiki Overview"
created: 2026-04-07
updated: 2026-06-03
tags:
  - meta
  - overview
status: developing
related:
  - "[[index]]"
  - "[[hot]]"
  - "[[log]]"
  - "[[dashboard]]"
  - "[[LLM Wiki Pattern]]"
sources:
---

# Wiki Overview

Navigation: [[index]] | [[hot]] | [[log]] | [[dashboard]]

---

## Purpose

This is the claude-obsidian demo vault. It demonstrates the [[LLM Wiki Pattern]] — a system for building persistent, compounding knowledge bases using Claude and Obsidian.

Run `/wiki` to scaffold this vault for your own domain and replace this overview.

---

## Current State

- **Sources ingested:** 8
- **Wiki pages:** 59
- **Last activity:** 2026-06-03 (coach-app fitness SaaS repo ingested)

---

## Concepts

**Knowledge Management:**
- [[LLM Wiki Pattern]] — the core architecture
- [[Hot Cache]] — session context mechanism
- [[Compounding Knowledge]] — why the pattern works
- [[DragonScale Memory]] — memory-layer spec with fold operator and semantic tiling
- [[Persistent Wiki Artifact]] — durable Markdown as LLM memory object
- [[Source-First Synthesis]] — provenance discipline
- [[Query-Time Retrieval]] — citation-aware query synthesis

**Architecture & Deployment:**
- [[Two-Repo Architecture]] — separating built app from infrastructure-as-code
- [[Nuxt.js Static Deployment]] — static generation with SPA fallback
- [[Vue.js SPA Static Deployment]] — compiled Vue.js bundles to S3
- [[Symfony ECS Deployment]] — containerized Symfony to AWS ECS Fargate
- [[JWT Auth in Static Sites]] — client-side JWT pattern
- [[ClearID Integration Architecture]] — Hermes systems integration with Genetec ClearID
- [[Serverless SaaS Architecture]] — React PWA + Lambda monolith + DynamoDB single-table on AWS

---

## Entities

- [[Andrej Karpathy]] — originated the LLM Wiki pattern
- [[Hermes International]] — French luxury design house; owner of HB8, Kellymorphose, Confie, ClearID, and 8 Penthièvre codebases
- [[Kellymorphose]] — Vue.js SPA (AWS S3)
- [[Confie]] — Symfony 7.2 PHP app (AWS ECS Fargate)
- [[ClearID]] — Genetec physical access control platform
- [[8 Penthièvre]] — Building website with interactive SVG floor plans
- [[Uplift Coach]] — Fitness coaching SaaS; React 19 PWA + Python Lambda + DynamoDB

---

## Sources

- [[claude-obsidian-ecosystem-research]] — 16+ projects, 13 cherry-picks identified (2026-04-08)
- [[hb8-app-repo]] — Nuxt.js static app for Hermes Haute Bijouterie 8 (2026-06-02)
- [[klm-repo]] — Vue.js SPA for Kellymorphose (2026-06-02)
- [[cnf-repo]] — Symfony 7.2 app for Confie (2026-06-02)
- [[clearid-directory]] — ClearID integration directory for Hermes (2026-06-03)
- [[livret-penthievre-sources]] — 8 Penthièvre building website (2026-06-03)
- [[coach-app-repo]] — Uplift Coach fitness SaaS (2026-06-03)

---

## Canvases

- [[claude-obsidian-presentation]] — Full presentation: hero, overview, skills, architecture, Wiki vs RAG, visual demos (2026-04-07)
- [[clearid-integration-architecture]] — ClearID data flow architecture (2026-06-03)
- [[hb8-cicd-pipeline]] — HB8 CI/CD pipeline visualization (2026-06-02)
- [[klm-cicd-pipeline]] — Kellymorphose CI/CD pipeline visualization (2026-06-02)
- [[cnf-cicd-pipeline]] — Confie CI/CD pipeline visualization (2026-06-02)
- [[coach-app-architecture]] — Uplift Coach architecture canvas (2026-06-03)
- [[coach-app-infra]] — AWS infrastructure diagram (draw.io) (2026-06-03)
- [[coach-app-auth-flow]] — Authentication flow sequence (draw.io) (2026-06-03)
- [[coach-app-data-model]] — DynamoDB single-table data model (draw.io) (2026-06-03)

---

## Key Themes

**Knowledge compounds.** Unlike RAG, the wiki pre-compiles synthesis. Cross-references are already there. Contradictions are flagged. Every ingest enriches existing pages rather than adding isolated chunks.

**The hot cache is the force multiplier.** A ~500-word file captures recent context. New sessions start with full context at minimal token cost.

**Obsidian is the IDE, Claude is the programmer.** The graph view shows what's connected. The human curates sources and asks questions. Claude writes and maintains everything else.
