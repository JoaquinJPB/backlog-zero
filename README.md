# Backlog Zero

Personal web app to log the video games you play: what you're playing, what you finished, ratings and dates, yearly lists, and (later) a social layer for friends.

Game data provided by [IGDB.com](https://www.igdb.com).

## Stack

- Laravel 13 (PHP 8.5 in the Sail container) + PostgreSQL 18
- Inertia 3 + React 19 + TypeScript + Tailwind 4 (official Laravel React starter kit, without its auth scaffolding)
- Laravel Sail (Docker) for every local service; Mailpit for local mail
- Pest, Larastan (level 7), Pint, vite-plus (`vp check`: oxlint + oxfmt), `tsc`

See `CLAUDE.md` for architecture rules and the MVP scope.

## Requirements

Everything runs inside **WSL2 (Ubuntu)** with **Docker Engine** available from WSL. Nothing (PHP, Composer, Node, databases) is installed on the host.

Keep the project in the Linux filesystem (e.g. `~/proyectos/backlog-zero`), never under `/mnt/c/...`.

## Setup (fresh clone)

```bash
git clone <repo-url> backlog-zero && cd backlog-zero

# Install PHP dependencies without local PHP (uses Sail's own composer image)
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd)":/var/www/html -w /var/www/html \
  laravelsail/php84-composer:latest composer install --ignore-platform-reqs

cp .env.example .env          # already set up for Sail (pgsql, mailpit)
./vendor/bin/sail up -d       # first run builds the image, takes a few minutes
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run build   # or `sail npm run dev` for the Vite dev server
```

App: http://localhost · Mailpit: http://localhost:8025

Tip: `alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'`

## Everyday commands

```bash
sail up -d                 # start containers
sail down                  # stop containers
sail artisan migrate       # run migrations
sail npm run dev           # Vite dev server
sail composer test         # Pint (check) + Larastan + Pest, same as CI
sail composer lint         # format PHP with Pint
sail npm run check         # frontend lint + format check
sail npm run check:fix     # frontend autofix
sail npm run types:check   # tsc --noEmit
```

## CI

`.github/workflows/tests.yml` runs on every push and pull request: frontend checks, `tsc`, Pint, Larastan and Pest against a PostgreSQL 18 service container.

## How this project was bootstrapped

For the record, these are the steps that actually worked (2026-11-27). They differ from the usual `laravel.build` one-liner because the React starter kit can only be installed when the app is created with `laravel new`, and the installer bundled in the `laravelsail/php84-composer` image is too old to know the `--react` flag.

```bash
cd ~/proyectos
docker run --rm -v "$(pwd)":/opt -w /opt laravelsail/php84-composer:latest bash -c '
  composer global require laravel/installer &&
  $(composer global config home)/vendor/bin/laravel new backlog-zero \
    --react --no-authentication --pest --database=pgsql --no-boost --no-interaction &&
  cd backlog-zero &&
  composer require laravel/sail --dev &&
  php artisan sail:install --with=pgsql,mailpit &&
  chown -R 1000:1000 /opt/backlog-zero'
```

- `--no-authentication`: auth will be Google OAuth via Socialite only (no passwords), so the kit's Fortify scaffolding is skipped.
- `--no-boost`: skips Laravel Boost and its generated `AGENTS.md`; `CLAUDE.md` is the single source of agent instructions.
- `chown` runs inside the container (which runs as root) so no `sudo` is needed on the host.

Then `sail up -d`, `sail npm install`, `sail npm run build`, `sail artisan migrate`. Afterwards `.env.example` was aligned with Sail's settings, `/CLAUDE.md` was removed from `.gitignore`, and the CI workflow got a PostgreSQL service.
