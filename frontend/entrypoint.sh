#!/bin/sh
set -e

# Asignar puerto por defecto si no esta definido
export PORT="${PORT:-80}"

# Normalizar BACKEND_URL agregando https:// si no contiene esquema http:// o https://
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

# Sustituir UNICAMENTE $PORT y $BACKEND_URL, preservando las variables internas de Nginx ($uri, $host, etc.)
envsubst '$PORT $BACKEND_URL' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

exec "$@"
