FROM php:8.1.3-cli-alpine
WORKDIR /data
RUN sed -i "s#https#\http#g" /etc/apk/repositories && \
    apk add  --no-cache imagemagick imagemagick-dev make icu-dev libpq-dev icu \
    && apk add --no-cache $PHPIZE_DEPS \
    &&  yes '' | pecl install http://pecl.php.net/get/imagick-3.7.0.tgz http://pecl.php.net/get/xdebug-3.1.4.tgz \
    && docker-php-ext-enable imagick xdebug\
    && docker-php-ext-install pdo_pgsql pgsql \
    && docker-php-ext-install sockets \
    && apk update
CMD ["php", "artisan", "queue:work"]
