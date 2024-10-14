# syntax=docker/dockerfile:1

FROM php:7.4.33-cli-alpine3.16

WORKDIR /var/www/html

RUN --mount=type=bind,from=mlocati/php-extension-installer:latest,source=/usr/bin/install-php-extensions,target=/usr/local/bin/install-php-extensions \
    ########################################
    apk add --update --no-cache git openssh \
 && install-php-extensions @composer zip gd pcntl xdebug

COPY composer.* /var/www/html/

ARG build_env=prod
ENV APP_ENV=$build_env

RUN --mount=type=secret,id=composer-auth,target=auth.json \
    ########################################
    if [ "$build_env" = "prod" ] ; then \
        composer install --no-interaction --no-dev --optimize-autoloader; \
    else \
        composer install --no-interaction; \
    fi \
 && composer clear-cache

COPY ./docker/*.ini /usr/local/etc/php/conf.d/
COPY ./docker/docker-entrypoint.sh /usr/local/bin/

COPY ./ppm.json /var/www/html/ppm.json

COPY ./src /var/www/html/src/

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["vendor/bin/ppm", "start"]
