FROM php:8.1.3-cli-alpine
COPY production/dockerfiles/configs/php-cli/php.ini /usr/local/etc/php/php.ini
RUN sed -i "s#https#\http#g" /etc/apk/repositories && \
    apk add  --no-cache imagemagick libpq-dev icu \
    && apk add --no-cache --virtual .build-deps autoconf imagemagick-dev gcc g++ make \
    && apk add --no-cache $PHPIZE_DEPS icu-dev g++ \
    && pecl install http://104.236.228.160/get/imagick-3.7.0.tgz \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install pdo_pgsql pgsql \
    && docker-php-ext-install sockets \
    && apk del .build-deps \
    && apk update

WORKDIR data
USER www-data
CMD ["php", "artisan", "queue:work"]
