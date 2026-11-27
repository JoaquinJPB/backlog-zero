#!/bin/sh
# Container entrypoint (production). Render's free plan has no pre-deploy
# command, so migrations run here, before the processes start.
set -e

cd /app

php artisan migrate --force
php artisan optimize

# FrankenPHP's default Caddyfile serves /app/public on $SERVER_NAME. A
# port-only address means plain HTTP: Render terminates TLS in front of us.
export SERVER_NAME=":${PORT:-10000}"

exec supervisord -n -c /etc/supervisor/conf.d/app.conf
