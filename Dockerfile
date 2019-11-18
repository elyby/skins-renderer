FROM php:7.3.11-cli-alpine3.10

WORKDIR /var/www/html

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
 && yes | pecl install xdebug-2.8.0 \
 && apk del ".phpize-deps" \
 && rm -rf /usr/share/man \
 && rm -rf /tmp/* \
 # Install composer and global dependencies
 && curl "https://getcomposer.org/download/1.9.1/composer.phar" -o /usr/bin/composer \
 && chmod a+x /usr/bin/composer \
 && mkdir /root/.composer \
 && echo '{"github-oauth": {"github.com": "81cbaaa04bb8f2c2fff61fa04870778e2a264052"}}' > /root/.composer/auth.json \
 && composer global require --no-progress "hirak/prestissimo:>=0.3.8" \
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

COPY ./ppm.json /var/www/html/ppm.json

COPY ./src /var/www/html/src/

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["vendor/bin/ppm", "start"]
