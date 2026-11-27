# CLAUDE.md — Backlog Zero

**Backlog Zero** is the app's name (use it in the UI, `APP_NAME`, page titles and the README). Repo/folder slug: `backlog-zero`.

Personal web app to log the video games I play: what I'm playing, what I finished, my rating and dates, lists per year, and later a social layer for friends. Not monetized. It will be deployed for a small group of friends, and the priority is that new features are easy to add without touching what already works.

## Who I am / how to work with me

- I'm a backend developer (PHP/Laravel, TypeScript, AWS). Don't explain Laravel basics; do explain non-obvious decisions.
- Talk to me in Spanish. Code, identifiers, commits and docs inside the repo are in English. User-facing UI strings in Spanish, through Laravel's translation files (`lang/`), never hard-coded.
- Propose a short plan before any change that touches more than one module or the database schema, and wait for my OK.
- Small, focused commits (Conventional Commits: `feat:`, `fix:`, `refactor:`, `test:`, `chore:`, `ci:`, `docs:`). **No `Co-Authored-By` / Claude attribution trailer.** Never push or force-push unless I ask.

## Environment (important)

- Everything runs inside **WSL2 (Ubuntu)**. Windows must stay clean: don't install PHP, Composer, Node or databases on Windows.
- The project lives in the Linux filesystem (`~/proyectos/backlog-zero`), **never under `/mnt/c/...`** (slow I/O and file-watching issues).
- Local services run with **Laravel Sail (Docker)**. Docker Engine runs natively inside WSL (not Docker Desktop). Prefer `./vendor/bin/sail <cmd>` (alias `sail`) for `artisan`, `composer`, `npm` and tests, so versions match the container.
- If something requires a host-level install, stop and ask first.

## First session: bootstrap

Done on 2026-11-27. The steps that actually worked (they differ from the list below: updated Laravel installer, React kit only via `laravel new`) are in `README.md`. Kept for reference:

1. Check prerequisites in WSL: `docker info`, `git --version`. If Docker isn't reachable from WSL, tell me (check the Docker Engine service in WSL) instead of working around it.
2. Create the app with the Laravel installer via Docker (no local PHP needed):
   `curl -s "https://laravel.build/backlog-zero?with=pgsql,mailpit" | bash`
3. `cd backlog-zero && sail up -d`, then add the React + TypeScript + Inertia starter kit and Tailwind (follow the current official Laravel docs for the starter kit; don't guess package names).
4. Add dev tooling: Pest, Larastan (level 6+), Laravel Pint, ESLint + Prettier for the frontend.
5. Set up `git init`, a sensible `.gitignore`, a first commit, and a GitHub Actions workflow that runs Pint (check), Larastan and Pest on every push.
6. Write a `README.md` with the setup steps that actually worked.
7. If this CLAUDE.md was started from the parent folder (`~/proyectos`), move it into `backlog-zero/` and commit it, so future sessions start from the project root.

## Stack

| Layer          | Choice                                                                                                                             |
| -------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| Backend        | Laravel 13 (PHP 8.3+; Sail runtime PHP 8.5)                                                                                        |
| Frontend       | Inertia + React + TypeScript, Tailwind                                                                                             |
| Database       | PostgreSQL                                                                                                                         |
| Queue / cache  | `database` driver to start. Add Redis + Horizon only when there's a measured need                                                  |
| Auth           | Laravel Socialite, Google OAuth only. **No passwords stored.** Adding Discord/Twitch later must be a config change, not a refactor |
| Game data      | IGDB API (Twitch OAuth2 client credentials)                                                                                        |
| Tests          | Pest (feature tests for every endpoint, unit tests for domain logic)                                                               |
| Quality        | Pint, Larastan (level 7), vite-plus `vp check` (oxlint + oxfmt, replaces ESLint/Prettier), `tsc`                                   |
| Deploy (later) | Laravel Cloud, or a VPS with Forge/Coolify. Not decided yet                                                                        |

## Architecture rules

- **Organize by domain, not by layer.** Each module is a folder under `app/Domain/<Module>` with its models, actions, events, policies and DTOs. HTTP controllers stay thin and call actions.
  Initial modules: `Users`, `Games` (catalog + IGDB), `Library` (a user's games: status, rating, dates, platform), `Journal` (play sessions/diary), `Lists`.
  Later: `Reviews`, `Social` (follows, activity feed), `Stats`, `Imports` (Steam, etc.).
- **Business logic lives in single-purpose Action classes** (`LogGameAction`, `FinishGameAction`…), reusable from controllers, jobs and commands.
- **Communication between modules goes through domain events** (`GameFinished`, `GameRated`…), not direct calls into another module's internals. New features subscribe to existing events.
- **External providers sit behind interfaces.** `Games` exposes a `GameCatalog` contract; `IgdbGameCatalog` is one implementation. Nothing outside `Games` talks to IGDB directly.
- Authorization with Policies. Validation with Form Requests. No business logic in Blade/React components.
- Ratings are stored as integers 1–10 (half stars), shown as 0.5–5 stars.

## IGDB rules (non-negotiable)

- Calls are **server-side only** (IGDB doesn't allow direct calls from the browser). Credentials live in `.env` (`IGDB_CLIENT_ID`, `IGDB_CLIENT_SECRET`) and are never committed.
- Cache the app access token until shortly before it expires.
- Respect the **4 requests/second** limit with Laravel's rate limiter (and queue throttling for background jobs).
- **Store games locally.** A game is imported into our `games` table the first time someone searches/adds it. Pages read from our DB, never from IGDB on every request. A scheduled job refreshes stale games.
- Images are referenced from IGDB's image CDN by `image_id`; don't download them.
- Show "Game data provided by IGDB.com" (with a link) in the site footer.
- In tests, never hit the real API: use `Http::fake()` with recorded fixtures.

## MVP scope (build in this order)

1. Google login + user profile with a unique username.
2. Game search (IGDB → local cache) and game page.
3. Add a game to my library with status (playing, finished, abandoned, backlog), rating, platform, start/finish dates.
4. Journal: chronological view of my sessions, filterable by year.
5. Yearly lists ("games finished in 2026") generated from the library, plus custom lists.
6. Public profile pages so friends can see each other's journal.
7. Import from a Backloggd-style CSV/XLSX (I already have my 2024–2026 history in spreadsheets).

Out of scope for now: reviews, likes, follows, feed, notifications, mobile app, Steam import.

## Commands

```bash
sail up -d                 # start containers
sail artisan migrate       # run migrations
sail npm run dev           # Vite dev server
sail composer test        # Pint (check) + Larastan + Pest, same as CI
sail composer lint        # format PHP with Pint
sail npm run check        # frontend lint + format check (vite-plus)
sail npm run check:fix    # frontend autofix
sail npm run types:check  # tsc --noEmit
```

Before saying a task is done: `sail composer test`, `sail npm run check` and `sail npm run types:check` must pass, and new behavior must have tests.
