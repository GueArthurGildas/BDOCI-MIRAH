---
name: phase-workflow
description: "Follow the refactoring plan against the BOID MIRAH specification: which phase is current, which decisions are settled, what must never be invented, and which documents to update together. Use for any work on the data model, modules M1-M12, roles, connectors or the plan, and before starting a new phase."
---

# Working the plan

## Read first

- `docs/PLAN_REFONTE.md`: phase table, status, decisions D1 to D9, parallel execution rules. All phases are delivered; Gildas's validation is pending; open questions are in `docs/QUESTIONS_GILDAS.md` and assumptions in `docs/assumptions/`.
- `docs/decisions/` (ADR 0001-0010): identifier language (English + glossary), PostgreSQL + PostGIS, flexible permissions, four roles (`admin`, `director`, `team_lead`, `agent`; the Admin has no business permission), reset from seeders, branch flow, validation, default thresholds, named actions.
- Specification (Gildas): `database/docs/` (**untracked, never commit**): the specification markdown, the MCD and the MySQL schema v1. Target schema in `docs/schema/`.

## Rules

1. **One phase at a time, validated by Gildas before the next** (business) and by the user (technical). Do not start a phase because it looks easy.
2. **Never invent a business rule.** If the specification (section 18 and the rest) does not define it, record it as an open question in the plan instead of coding a guess. Currently open: status light rules, which infractions are "open", which license counts when a vessel has several, generic tags absent from the SQL.
3. The specification wins over the current code (for example VMS silence 4 h and license alerts 30/60/90 days, applied in phases 3b and 4a).
4. Existing features are a simplified version of the target: map them with `docs/GLOSSAIRE.md` section 4 before reshaping a table.
5. No deployed database exists: prefer clean migrations and `migrate:fresh --seed` over data migrations.

## Documents that move together

A change to the model or a decision touches, as relevant: `docs/schema/schema_v2.sql`, `docs/schema/GLOSSAIRE_COLONNES.md`, `docs/schema/CONVENTIONS.md` (deviation table), `docs/GLOSSAIRE.md` (tests fail if a table is missing), a new ADR (never rewrite an old one: add a new number and mark the old one superseded), `docs/ACCEPTATION.md` (UC/RG traceability), `docs/GUIDE.md` (recipes), and the phase block in `docs/PLAN_REFONTE.md`.

## Style

Docs in `docs/` are French; code, identifiers, commit messages and skills are English; chat with the user in French. No comments in code. Everything displayed goes through `lang/{en,fr}`.
