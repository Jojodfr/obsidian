---
type: meta
title: "Concepts Index"
updated: 2026-06-03
tags:
  - meta
  - index
  - concept
domain: knowledge-management
status: evergreen
related:
  - "[[index]]"
  - "[[dashboard]]"
  - "[[Wiki Map]]"
  - "[[Hot Cache]]"
  - "[[LLM Wiki Pattern]]"
  - "[[Compounding Knowledge]]"
  - "[[LLM Wiki Pattern]]"
  - "[[Hot Cache]]"
  - "[[Compounding Knowledge]]"
---

# Concepts Index

Navigation: [[index]] | [[entities/_index|Entities]] | [[sources/_index|Sources]]

All concept pages — ideas, patterns, and frameworks extracted from sources.

---

## Knowledge Management

- [[LLM Wiki Pattern]] — the core architecture for persistent, compounding knowledge bases
- [[Hot Cache]] — ~500-word session context file, updated after every ingest
- [[Compounding Knowledge]] — why the wiki grows more valuable over time, unlike RAG
- [[DragonScale Memory]] — memory-layer spec: fold operator, deterministic page addresses, semantic tiling, boundary-first autoresearch (status: shipped v0.4, all four mechanisms opt-in)
- [[Persistent Wiki Artifact]]: durable Markdown page as the LLM's memory object (developing)
- [[Source-First Synthesis]]: provenance discipline for LLM wiki layers (developing)
- [[Query-Time Retrieval]]: query synthesis with citations, complementary to Obsidian search (developing)

---

- [[Two-Repo Architecture]] — separating built app from infrastructure-as-code (status: mature)
- [[Nuxt.js Static Deployment]] — static generation with SPA fallback and performance techniques (status: mature)
- [[Vue.js SPA Static Deployment]] — compiled Vue.js bundles to S3, client-side routing (status: mature)
- [[Symfony ECS Deployment]] — containerized Symfony to AWS ECS Fargate via Kaniko (status: mature)
- [[JWT Auth in Static Sites]] — client-side JWT pattern without backend (status: developing)
- [[ClearID Integration Architecture]] — Hermes systems integration with Genetec ClearID; data flows, auth patterns, deployment (status: current)
- [[Serverless SaaS Architecture]] — React PWA + Lambda monolith + DynamoDB single-table on AWS; distilled from [[Uplift Coach]] (status: developing)
- [[Ticketing System Architecture]] — Symfony API Platform + Angular SPA pattern for internal maintenance ticketing; from [[Shop Maintenance App]] (status: developing)

## Add new concepts here as they are extracted from sources.
