FROM cr.userstory.ru/docker/php-fpm-backend/php8.1:latest
WORKDIR /data
RUN sed -i "s#https#\http#g" /etc/apk/repositories && \
    apk add  --no-cache make icu-dev libpq-dev icu pngquant \
    && curl -O https://www.jonof.id.au/files/kenutils/pngout-20200115-linux-static.tar.gz \
    && tar zxf pngout-20200115-linux-static.tar.gz \
    && cd pngout-20200115-linux-static \
    && cp -f amd64/pngout-static /usr/local/bin/pngout \
    && apk add --no-cache $PHPIZE_DEPS \
    &&  yes '' | pecl install http://pecl.php.net/get/xdebug-3.1.4.tgz \
    && docker-php-ext-enable xdebug\
    && docker-php-ext-install pdo_pgsql pgsql \
    && docker-php-ext-install sockets \
    && apk update

RUN wget https://getcomposer.org/composer-2.phar && \
    mv composer-2.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer
