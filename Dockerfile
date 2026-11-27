# Production image (Render). Local development uses Sail, not this file.
#
# One container runs three processes under supervisord: FrankenPHP (web),
# the queue worker and the scheduler. Render's free plan has no separate
# workers or cron jobs, so they live here.

ARG PHP_IMAGE=dunglas/frankenphp:1-php8.5-trixie

# --- Build: PHP deps + frontend assets (Wayfinder needs PHP during the Vite build)
FROM ${PHP_IMAGE} AS build

RUN install-php-extensions pdo_pgsql intl zip pcntl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:24-trixie-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:24-trixie-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --no-autoloader --prefer-dist

COPY package.json package-lock.json .npmrc ./
RUN npm ci --no-audit --no-fund

COPY . .

ENV VITE_APP_NAME="Backlog Zero"
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev \
    && npm run build \
    && rm -rf node_modules

# --- Runtime
FROM ${PHP_IMAGE}

RUN install-php-extensions pdo_pgsql intl zip pcntl opcache \
    && apt-get update \
    && apt-get install -y --no-install-recommends supervisor \
    && rm -rf /var/lib/apt/lists/* \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /app

COPY --from=build /app /app
COPY docker/supervisord.conf /etc/supervisor/conf.d/app.conf
COPY docker/start.sh /usr/local/bin/start

# Run as an unprivileged user; Render's port (10000) needs no extra capability.
# The base image grants frankenphp cap_net_bind_service (for ports 80/443).
# Render runs containers with capabilities dropped, and the kernel then
# refuses to exec a binary carrying that file capability (EPERM), so strip it.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libcap2-bin \
    && setcap -r /usr/local/bin/frankenphp \
    && apt-get purge -y libcap2-bin \
    && rm -rf /var/lib/apt/lists/* \
    && useradd --create-home --uid 1000 app \
    && chown -R app:app /app/storage /app/bootstrap/cache /data/caddy /config/caddy \
    && chmod +x /usr/local/bin/start

USER app

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PORT=10000

EXPOSE 10000

CMD ["start"]
