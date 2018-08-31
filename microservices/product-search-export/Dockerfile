FROM php:7.2-fpm-alpine

# install Composer
COPY docker/docker-install-composer /usr/local/bin/docker-install-composer
RUN chmod +x /usr/local/bin/docker-install-composer && docker-install-composer

# install necessary PHP extension
RUN docker-php-ext-install \
    opcache

# install locales and switch to en_US.utf8 in order to enable UTF-8 support
# see https://github.com/docker-library/php/issues/240#issuecomment-305038173
RUN apk add --update --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/edge/testing gnu-libiconv
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
ENV LC_ALL=en_US.utf8 LANG=en_US.utf8 LANGUAGE=en_US.utf8

# overwrite the original entry-point from the PHP Docker image with our own
COPY docker/docker-php-entrypoint /usr/local/bin/

# copy source code to container
COPY / /var/www/html

# copy php configuration
COPY docker/php-ini-overrides.ini /usr/local/etc/php/php.ini

ARG github_oauth_token
RUN composer config -g github-oauth.github.com $github_oauth_token
RUN composer install

# start symfony webserver
CMD php bin/console server:run *:8000
