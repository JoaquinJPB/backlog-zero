# CLAUDE.md — Backlog Zero

**Backlog Zero** is the app's name (UI, `APP_NAME`, page titles, README). Repo/folder slug: `backlog-zero`.

Web app to log the video games we play: what we're playing, what we finished, ratings and dates, a yearly journal, and later a social layer between friends. Not monetized. Deployed for a small group of friends; the priority is that new features are easy to add without touching what already works.

The project owner started it; **friends now contribute through GitHub**. These rules apply to every contributor and every Claude session.

## How to work here

- Contributors are developers (the owner is a PHP/Laravel + TypeScript backend dev). Don't explain Laravel basics; do explain non-obvious decisions.
- Talk to the person in Spanish unless they write in another language. Code, identifiers, commits, docs and comments in the repo are in English. **User-facing strings are Spanish and live in `lang/es/*.php`, never hard-coded** (see i18n below).
- Propose a short plan before any change that touches more than one module or the database schema, and wait for the person's OK.
- Small, focused commits with Conventional Commits (`feat:`, `fix:`, `refactor:`, `test:`, `chore:`, `ci:`, `docs:`). **No `Co-Authored-By` / Claude attribution trailer.**
- **Never push or force-push unless the person asks.** With several contributors, work on a **feature branch and open a PR** to `main`; don't push straight to `main`. CI must be green before merging.
- Before saying a task is done: `sail composer test`, `sail npm run check` and `sail npm run types:check` pass, and new behavior has tests.

## Environment

- Everything runs inside **WSL2 (Ubuntu)** / Linux. Don't install PHP, Composer, Node or databases on the Windows host.
- Keep the repo in the Linux filesystem (e.g. `~/proyectos/backlog-zero`), **never under `/mnt/c/...`**.
- Local services run with **Laravel Sail (Docker Engine)**. Use `./vendor/bin/sail <cmd>` (alias `sail`) for `artisan`, `composer`, `npm` and tests so versions match the container (PHP 8.5, Node 24, Postgres 18, Mailpit).
- If something needs a host-level install, stop and ask first.
- Setup from a fresh clone, Google OAuth and IGDB credentials: see `README.md`. Secrets only in `.env` (gitignored): `GOOGLE_CLIENT_ID/SECRET`, `IGDB_CLIENT_ID/SECRET`. Never paste them in chats, commits or fixtures.

## Stack

| Layer         | Choice                                                                                                                    |
| ------------- | ------------------------------------------------------------------------------------------------------------------------- |
| Backend       | Laravel 13 (PHP 8.3+; Sail runtime PHP 8.5)                                                                               |
| Frontend      | Inertia 3 + React 19 + TypeScript + Tailwind 4 (official React starter kit, no auth scaffolding), Wayfinder route helpers |
| Database      | PostgreSQL 18 (CHECK constraints are used; Postgres-only is fine)                                                         |
| Queue / cache | `database` driver. Add Redis + Horizon only when there's a measured need                                                  |
| Auth          | Socialite, Google only. **No passwords.** Providers listed in `AUTH_SOCIAL_PROVIDERS`                                     |
| Game data     | IGDB API v4 (Twitch client credentials)                                                                                   |
| Tests         | Pest 5 (feature tests per endpoint, unit tests for pure domain logic)                                                     |
| Quality       | Pint, Larastan level 7, vite-plus `vp check` (oxlint + oxfmt; replaces ESLint/Prettier), `tsc`                            |
| CI            | `.github/workflows/tests.yml` on every push/PR, with a Postgres 18 service                                                |
| Deploy        | Render free web service (Docker: `Dockerfile`, `render.yaml`) + Neon free Postgres. Setup in README "Deploy"              |

## Architecture rules

- **Organize by domain.** Each module lives in `app/Domain/<Module>` (models, actions, events, policies, DTOs, queries). HTTP controllers in `app/Http` stay thin: Form Request → Action → Resource → `Inertia::render`.
- **Business logic in single-purpose Action classes**, reusable from controllers, jobs and commands. DTOs enforce domain invariants for every caller; Form Requests only turn them into friendly Spanish messages first; Postgres CHECK constraints are the last line of defence.
- **Between modules:** react to changes through **domain events** (dispatched after commit); read another module's data only through its **public query classes** (e.g. `Library\Queries\FinishedGamesQuery`), never its tables directly.
- **External providers sit behind interfaces.** `Games\Contracts\GameCatalog` → `IgdbGameCatalog`. Nothing outside `Games` talks to IGDB.
- Authorization with Policies (`#[UsePolicy]`), validation with Form Requests, no business logic in React components.
- Shared Inertia props (`HandleInertiaRequests`): `auth.user` (id, name, username, avatar_url only), `locale`, and once-props `translations` and `authProviders`. Flash messages via `Inertia::flash('success'|'error', …)`.
- Ratings are integers 1–10, entered as five half-stars and always shown as the whole number (5 stars = 10, 4½ = 9, 1 = 2). No fractions or decimals on a single rating; averages keep one decimal.

### i18n

- PHP: `__('ui.group.key')`, validation messages in `lang/es/validation.php`.
- React: `const { t, tc } = useTranslation()` from `@/lib/i18n`; `t('group.key', {name})` reads `lang/es/ui.php`; `tc()` handles Laravel plural strings (`{0} …|{1} …|[2,*] :count …`).

## IGDB rules (non-negotiable)

- **Server-side only.** Credentials in `.env`. The token request sends the secret in the form body (not the query string), so connection errors can't leak it to logs.
- App token cached until 5 min before expiry; refreshed on 401.
- **4 requests/second** via a shared rate limiter; 429s retried with backoff. Background refresh jobs have their own 2/s limiter (`igdb-jobs`).
- **Store games locally.** Search results are upserted into `games` (+ platforms, genres); pages read only our DB. `games:refresh-stale` (daily 04:00) queues `RefreshGameJob` for games not synced in 7 days; viewing a stale page also queues one.
- Covers are hot-linked from IGDB's CDN by `image_id` (`@/lib/igdb`), never downloaded.
- Footer shows "Game data provided by IGDB.com" with a link.
- Tests never hit real APIs: `Http::preventStrayRequests()` is on for the whole suite. Use `Http::fake()` with fixtures in `tests/Fixtures/igdb` (recorded from the real API; the token fixture is fake).
- Search = IGDB full-text search **plus** a `name ~ *"…"*` match (full-text drops common words: "We Were Here" found nothing), merged and ordered by `IgdbSearchRanker`: main/remake/remaster first, then ports/expansions, then bundles/DLC/packs/mods/updates; exact name and dated games first within a tier. Nothing is filtered except `version_parent != null` (editions). One uncached search = 2 IGDB requests.

## What exists (status 2026-11-27)

| Module / area | What it does                                                                                                                                                                                                                                                                                                                                                                                                                     |
| ------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Users`       | Google login (`/auth/{provider}/…`), identities in `social_accounts` matched by provider id (**never auto-linked by email**), `UserRegistered` event. Sign-up limited to `ALLOWED_EMAILS` when set. Mandatory username onboarding (`/onboarding/username`, 3–20 `[a-z0-9_]`, lowercase, reserved words). Profile settings (`/settings/profile`).                                                                                 |
| `Games`       | `GameCatalog` + IGDB client, `/games/search` (signed-in, 30/min, cached 6 h), public game page `/games/{slug}`, background refresh of stale games.                                                                                                                                                                                                                                                                               |
| `Library`     | `library_entries` (one per user+game): status playing/finished/abandoned/backlog, rating, 100% flag (`completed`, not allowed in backlog; gold trophy in the UI), platform (only the game's IGDB platforms), start/finish dates. Form on the game page, `/library` with status tabs. Events: `GameAddedToLibrary`, `GameFinished`, `GameRated`, `GameRemovedFromLibrary` (no listeners yet). Public query: `FinishedGamesQuery`. |
| `Journal`     | `/journal`: games finished in a year grouped by month, summary (count, average rating), filters: year, platform, include abandoned. No own tables.                                                                                                                                                                                                                                                                               |
| `Stats`       | `/u/{username}` profile (owner-only for now via `UserPolicy::viewProfile`, others get 404): counts per status, finished all-time/this year, average rating + histogram, finished per year (links to journal), top 5 platforms and genres, paginated grid of played games (not backlog). No own tables: reads `Library\Queries\LibraryStatsQuery` + `FinishedGamesQuery`.                                                         |

Header links (signed-in): Buscar juegos · Mi biblioteca · Journal · @username (profile; "Editar perfil" there goes to settings) · Cerrar sesión.

## Roadmap

Done: 1) Google login + username · 2) IGDB search + game page · 3) Library · 4) Journal (redefined: yearly "finished games" view instead of a session diary) · 5) Profile + Stats (owner-only).

Next, in order:

6. **Public profiles** so friends see each other's profile and journal (privacy setting): relax `UserPolicy::viewProfile` and add guest access. Public URLs are `/u/{username}`.
7. **Custom lists** (`Lists` module). Yearly lists are covered by the Journal — _owner still to confirm this scope change._
8. **Import** from Backloggd-style CSV/XLSX (owner's 2024–2026 history). Must go through `LibraryEntryData` + library actions so all invariants apply.

Out of scope for now: reviews, likes, follows, feed, notifications, mobile app, Steam import, auto-switching a game to "playing".

## Commands

```bash
sail up -d                 # start containers
sail artisan migrate       # run migrations
sail npm run dev           # Vite dev server (or: sail npm run build)
sail artisan queue:listen  # queue worker (background IGDB refreshes)
sail artisan schedule:work # scheduler (games:refresh-stale daily)
sail artisan pail          # tail the app log
sail composer test         # Pint (check) + Larastan + Pest, same as CI
sail composer lint         # format PHP with Pint
sail npm run check         # frontend lint + format check (vite-plus; also formats Markdown/JSON)
sail npm run check:fix     # frontend autofix
sail npm run types:check   # tsc --noEmit (run a build or `sail artisan wayfinder:generate` first so route helpers exist)
```

## Gotchas

- The kit's `.gitignore` used to ignore `/CLAUDE.md`; it's versioned now on purpose.
- PHP tests run without Vite (`withoutVite()` in `TestCase`); Inertia's `ensure_pages_exist` still fails if a page file is missing.
- In Pest, the first matching `Http::fake()` wins: fake per test, not in `beforeEach` plus overrides.
- Faker's `words()` is typed `array|string`; use `sentence()` in factories to keep Larastan happy.
- Routes: `games/search` is declared before `games/{game}`; the slug `search` is reserved by `UpsertGameAction`.
- `vp check` formats `*.md` and fixtures too: run `sail npm run check:fix` before committing docs.
- Production = one container (`Dockerfile`): FrankenPHP + `queue:work` + `schedule:work` under supervisord. `docker/start.sh` runs `migrate --force` on every boot, so **migrations must be backward compatible** (the old version keeps serving during a deploy). Free plan sleeps after ~15 min idle (worker/scheduler sleep too); an external ping to `/up` keeps it awake.
- `trustProxies(at: '*')` in `bootstrap/app.php` is needed behind Render's proxy (https URLs, OAuth callback).
- Sign-up is limited by `ALLOWED_EMAILS` (`auth.allowed_emails`, checked in `AuthenticateWithProviderAction` only for new accounts). Empty = open (local, tests). Google's OAuth _Testing_ mode is **not** our access control.
- Any new env var used in production must be added to `render.yaml` (`sync: false` for secrets) and the README table.
