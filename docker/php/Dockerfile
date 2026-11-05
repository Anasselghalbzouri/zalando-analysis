# Laravel PHP-FPM image for local development. Used by the app, scheduler and queue services
# (docs/architecture/docker-architecture.md §1). Not a production image.
FROM php:8.4-fpm-alpine

ARG UID=1000
ARG GID=1000

# Runtime libraries, plus fcgi for the PHP-FPM ping health check.
RUN apk add --no-cache icu-libs libzip fcgi \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev linux-headers \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql intl bcmath opcache pcntl zip \
    # phpredis, only used when the optional "redis" profile is enabled (ADR-007).
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY docker/php/conf.d/app.ini "$PHP_INI_DIR/conf.d/zz-app.ini"
COPY docker/php/php-fpm.d/zz-app.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY --chmod=755 docker/php/entrypoint.sh /usr/local/bin/app-entrypoint

# Run as an unprivileged user whose UID/GID match the host user (bind-mounted code stays writable).
RUN addgroup -g "${GID}" app \
    && adduser -D -u "${UID}" -G app -h /home/app app \
    && mkdir -p /var/www/html/vendor \
    && chown -R app:app /var/www/html

WORKDIR /var/www/html
USER app

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]
