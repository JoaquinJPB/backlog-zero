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

## Google sign-in (local)

There are no passwords: users sign in with Google (Socialite). Tests fake the provider, so this is only needed to sign in from the browser.

1. In [Google Cloud Console](https://console.cloud.google.com/apis/credentials), create an **OAuth client ID** of type _Web application_ (configure the consent screen first if asked; _External_ + test users is enough).
2. Authorized redirect URI: `http://localhost/auth/google/callback`
3. Put the credentials in `.env` (never commit them):

    ```dotenv
    GOOGLE_CLIENT_ID=...
    GOOGLE_CLIENT_SECRET=...
    ```

4. `sail artisan config:clear`, open http://localhost and click _Entrar con Google_. On first sign-in you'll be asked to pick a username.

Enabled providers are listed in `AUTH_SOCIAL_PROVIDERS` (comma-separated). Adding another one means installing its Socialite driver, adding its credentials to `config/services.php` and listing it there.

## IGDB game data (local)

Game search uses the [IGDB API](https://api-docs.igdb.com/) through a Twitch developer app. Tests fake every IGDB call, so this is only needed to search from the browser.

1. Have a Twitch account with two-factor authentication enabled.
2. Register an app at https://dev.twitch.tv/console/apps/create: OAuth Redirect URL `http://localhost` (IGDB doesn't use it), Client Type **Confidential**.
3. In _Manage_, press _New Secret_ and put both values in `.env` (never commit them):

    ```dotenv
    IGDB_CLIENT_ID=...
    IGDB_CLIENT_SECRET=...
    ```

4. `sail artisan config:clear`, sign in and use _Buscar juegos_.

The API is free for non-commercial use under the Twitch Developer Services Agreement. Searched games are stored in our database and pages read only from it; stale games are refreshed by a queued job, so run a worker and the scheduler while developing:

```bash
sail artisan queue:listen      # processes background refreshes
sail artisan schedule:work     # runs games:refresh-stale daily
```

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

## Deploy (Render + Neon, free plans)

Production runs on a [Render](https://render.com) free web service (Docker) with a [Neon](https://neon.tech) free PostgreSQL database. Free-plan limits change; check both sites before relying on them.

- `Dockerfile` builds the production image (PHP 8.5 + FrankenPHP; assets are built inside the image). Sail is not involved.
- One container runs the web server, the queue worker and the scheduler under supervisord (`docker/supervisord.conf`): the free plan has no separate workers or cron jobs.
- On every start `docker/start.sh` runs `migrate --force` and `optimize` (the free plan has no pre-deploy command). **Migrations must therefore be safe to run while the previous version is still serving.**
- `render.yaml` (Blueprint) defines the service. Render deploys `main` automatically, but only once the GitHub checks (CI) pass.

### First-time setup

1. **Neon:** create a project in region _AWS Europe Central 1 (Frankfurt)_ and copy the **direct** (non-pooled) connection string. It looks like `postgresql://user:password@ep-xxx.eu-central-1.aws.neon.tech/neondb?sslmode=require`.
2. **Render:** _New → Blueprint_, pick this GitHub repo; Render reads `render.yaml` and asks for the secret values:

    | Variable                  | Value                                                                                    |
    | ------------------------- | ---------------------------------------------------------------------------------------- |
    | `APP_KEY`                 | output of `sail artisan key:generate --show` (a new key, not your local one)             |
    | `APP_URL`                 | the service URL, e.g. `https://backlog-zero.onrender.com` (**https**, no trailing slash) |
    | `DB_URL`                  | the Neon connection string from step 1                                                   |
    | `GOOGLE_CLIENT_ID/SECRET` | same OAuth client as local is fine (see step 3)                                          |
    | `IGDB_CLIENT_ID/SECRET`   | same Twitch app as local is fine                                                         |

3. **Google OAuth:** in the OAuth client, add the authorized redirect URI `https://<your-service>.onrender.com/auth/google/callback`. Who can sign up is controlled here: keep the consent screen in **Testing** and add each friend's Google email under _Test users_ (max. 100). Anyone else is rejected by Google.
4. **Keep it awake (optional, recommended):** free services sleep after ~15 min without traffic and take about a minute to wake up. A free monitor such as [cron-job.org](https://cron-job.org) calling `https://<your-service>.onrender.com/up` every 10 minutes keeps it awake.

Logs: Render dashboard → service → _Logs_ (everything goes to stderr).

### Testing the production image locally

```bash
docker build -t backlog-zero:prod .
```

To run it you need a database it can reach and a throwaway `APP_KEY`; pass the same variables as in `render.yaml` with `-e` and publish port 10000.

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
