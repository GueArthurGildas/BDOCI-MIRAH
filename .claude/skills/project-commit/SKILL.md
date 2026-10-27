---
name: project-commit
description: Commit conventions for this repository (branch, what to stage, message style, attribution, what never to commit). Use whenever the user asks to commit, group changes into commits, or prepare a push.
---

# Committing

- **Only commit when asked.** Never push, merge into `main`, or create branches unless explicitly requested. Work happens on `y-branch`; `main` is the deployment branch; `g-branch` is the coworker's finished design branch.
- **Never stage `database/docs/`** (the specification given by Gildas, kept untracked). Never commit `.env`, scratch files or screenshots.
- Stage **named paths** (`git add app modules docs ...`), then `git status --short` to confirm nothing unexpected is staged. If the user asks for N commits, split by theme (for example roles / infrastructure / documentation) and put every file in exactly one commit; documents that mix themes go with the documentation commit.
- **Message**: imperative English title, no prefix, about 70 characters, then a short body that explains the why. Pass it with a HEREDOC. End with the attribution trailer given in the session instructions (`Co-Authored-By: ...`).
- Run the full suite before committing code (`docker compose exec -T app php artisan test`); a docs-only commit does not need it, except that `GlossaryTest` reads `docs/GLOSSAIRE.md` and `docs/schema/`.
- Never amend or force-push without being asked. If a hook fails, fix and create a new commit.
- After committing, report the short hash and `git status`.
