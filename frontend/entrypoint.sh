#!/bin/sh
set -e

export PORT="${PORT:-80}"

if [ -n "$BACKEND_URL" ]; then
    case "$BACKEND_URL" in
        http://*|https://*)
            ;;
        *)
            export BACKEND_URL="https://${BACKEND_URL}"
            ;;
    esac
else
    export BACKEND_URL="http://backend:80"
fi

envsubst '$PORT $BACKEND_URL' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

exec "$@"
