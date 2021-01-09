FROM php:7.4.14-cli-alpine3.12

# bash needed to support wait-for-it script
RUN apk add --update --no-cache \
    git \
    openssh \
    # zip extension
    libzip-dev \
    # gd extension
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
 && docker-php-ext-configure gd \
    --with-freetype-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ \
 && docker-php-ext-install -j$(nproc) gd zip pcntl opcache \
 && apk add --no-cache --virtual ".phpize-deps" $PHPIZE_DEPS \
 && yes | pecl install xdebug-2.9.8 \
 && apk del ".phpize-deps" \
 && rm -rf /usr/share/man \
 && rm -rf /tmp/* \
 # Install composer and global dependencies
 && curl "https://getcomposer.org/download/2.0.8/composer.phar" -o /usr/bin/composer \
 && chmod a+x /usr/bin/composer \
 # TODO: migrate to the build-pack secrets when they will implement compatibility with the docker-compose
 # Feature: https://docs.docker.com/develop/develop-images/build_enhancements/#new-docker-build-secret-information
 # Track issues: https://github.com/docker/compose/issues/6358, https://github.com/compose-spec/compose-spec/issues/81
 && composer global config github-oauth.github.com "81cbaaa04bb8f2c2fff61fa04870778e2a264052"

COPY ./composer.* /var/www/html/

ARG build_env=prod
ENV APP_ENV=$build_env

RUN if [ "$build_env" = "prod" ] ; then \
        composer install --no-interaction --no-suggest --no-dev --optimize-autoloader; \
    else \
        composer install --no-interaction --no-suggest; \
    fi \
 && composer clear-cache

COPY ./docker/*.ini /usr/local/etc/php/conf.d/
COPY ./docker/docker-entrypoint.sh /usr/local/bin/

COPY ./ppm.json /var/www/html/ppm.json

COPY ./src /var/www/html/src/

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["vendor/bin/ppm", "start"]
