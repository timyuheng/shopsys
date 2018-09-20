FROM php:7.2-fpm-alpine as basic

# install necessary PHP extension
RUN docker-php-ext-install \
    opcache

COPY docker/php-ini-overrides.ini /usr/local/etc/php/php.ini

# install locales and switch to en_US.utf8 in order to enable UTF-8 support
# see https://github.com/docker-library/php/issues/240#issuecomment-305038173
RUN apk add --update --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/edge/testing gnu-libiconv
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
ENV LC_ALL=en_US.utf8 LANG=en_US.utf8 LANGUAGE=en_US.utf8

# overwrite the original entry-point from the PHP Docker image with our own
COPY docker/docker-php-entrypoint /usr/local/bin/

RUN chown www-data:www-data /var/www/html

WORKDIR /var/www/html

########################################################################################################################

FROM basic as development

ENV APP_ENV dev

# configure docker command that properly starts the microservice
COPY docker/docker-after-start /usr/local/bin/docker-after-start
RUN chmod +x /usr/local/bin/docker-after-start
CMD docker-after-start

# install Composer
COPY docker/docker-install-composer /usr/local/bin/docker-install-composer
RUN chmod +x /usr/local/bin/docker-install-composer && docker-install-composer

# allow overwriting UID and GID o the user "www-data" to help solve issues with permissions in mounted volumes
# if the GID is already in use, we will assign GID 82 instead (82 is the standard uid/gid for "www-data" in Alpine)
ARG www_data_uid
ARG www_data_gid
RUN if [ -n "$www_data_uid" ]; then deluser www-data && (addgroup -g $www_data_gid www-data || addgroup -g 82 www-data) && adduser -u $www_data_uid -D -S -G www-data www-data; fi

# allow configuring the GitHub OAuth token
ARG github_oauth_token
RUN composer config -g github-oauth.github.com $github_oauth_token

USER www-data

########################################################################################################################

FROM php:7.2-fpm-alpine as prepare-production

ENV APP_ENV prod

# install Composer
COPY docker/docker-install-composer /usr/local/bin/docker-install-composer
RUN chmod +x /usr/local/bin/docker-install-composer && docker-install-composer

# allow configuring the GitHub OAuth token
ARG github_oauth_token
RUN composer config -g github-oauth.github.com $github_oauth_token
# install composer dependencies (as www-data user)

# copy source code to the container
COPY --chown=www-data:www-data / /var/www/html

RUN composer install -o --no-dev

########################################################################################################################

FROM basic as production

ENV APP_ENV prod

# copy source code to the container
COPY --chown=www-data:www-data / /var/www/html

COPY --from=prepare-production --chown=www-data:www-data /var/www/html/vendor /var/www/html/vendor

# warm up the Symfony cache
RUN php bin/console cache:warmup

# start the PHP server
CMD php bin/console server:run *:8000
