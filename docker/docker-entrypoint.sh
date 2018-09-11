#!/usr/bin/env sh
set -e

XDEBUG_EXTENSION_FILE="/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
PHP_PROD_INI="/usr/local/etc/php/conf.d/php.prod.ini"
PHP_DEV_INI="/usr/local/etc/php/conf.d/php.dev.ini"

APP_ENV=$(echo "${APP_ENV:-prod}")
APP_DEBUG=$(echo "${APP_DEBUG:-false}" | awk '{print tolower($0)}')
case "$APP_DEBUG" in
    "true" | "on" | "yes" | "1")
        IS_DEBUG=1
        ;;
    *)
        IS_DEBUG=0
        ;;
esac

if [ "$APP_ENV" = "dev" ] ; then
    echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > $XDEBUG_EXTENSION_FILE
    mv ${PHP_PROD_INI}{,.disabled} 2> /dev/null || true
    mv ${PHP_DEV_INI}{.disabled,} 2> /dev/null || true
else
    rm -f $XDEBUG_EXTENSION_FILE
    mv ${PHP_DEV_INI}{,.disabled} 2> /dev/null || true
    mv ${PHP_PROD_INI}{.disabled,} 2> /dev/null || true
fi

if [ "$1" = "sh" ] || [ "$1" = "composer" ] || [ "$1" = "php" ] ; then
    exec "$@"
    exit 0
fi

exec "$@" --debug $IS_DEBUG --app-env $APP_ENV --debug $IS_DEBUG
