FROM php:7.2.9-cli-alpine3.8

WORKDIR /var/www/html
ENV COMPOSER_ALLOW_SUPERUSER 1

# bash needed to support wait-for-it script
RUN apk add --update --no-cache \
    git \
    openssh \
    # zip extension
    zlib-dev \
    # gd extension
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
 && docker-php-ext-configure gd \
    --with-freetype-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ \
 && docker-php-ext-install -j$(nproc) gd zip pcntl opcache \
 && apk add --no-cache --virtual ".phpize-deps" $PHPIZE_DEPS \
 && yes | pecl install xdebug-2.6.1 \
 && apk del ".phpize-deps" \
 && rm -rf /usr/share/man \
 && rm -rf /tmp/*

COPY --from=composer:1.7.2 /usr/bin/composer /usr/bin/composer

RUN mkdir /root/.composer \
 && echo '{"github-oauth": {"github.com": "81cbaaa04bb8f2c2fff61fa04870778e2a264052"}}' > ~/.composer/auth.json \
 && composer global require --no-progress "hirak/prestissimo:^0.3.7" \
 && composer clear-cache

COPY ./composer.* /var/www/html/

ARG build_env=prod
ENV APP_ENV=$build_env

RUN if [ "$build_env" = "prod" ] ; then \
        composer install --no-interaction --no-suggest --no-dev; \
    else \
        composer install --no-interaction --no-suggest; \
    fi \
 && composer clear-cache

COPY ./docker/*.ini /usr/local/etc/php/conf.d/
COPY ./docker/docker-entrypoint.sh /usr/local/bin/

COPY ./src /var/www/html/src/
COPY ./ppm.json /var/www/html/ppm.json

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["vendor/bin/ppm", "start"]
