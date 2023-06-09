FROM cr.userstory.ru/docker/php-composer2-alpine/php8.1:latest as vendor
COPY . /data
WORKDIR /data
RUN composer install

FROM cr.userstory.ru/docker/php-fpm-backend/php8.1:latest
WORKDIR /data
COPY --chown=www-data:www-data . /data
COPY --from=vendor --chown=www-data:www-data /data/vendor /data/vendor
COPY production/dockerfiles/configs/php-fpm/ext-cfg.ini /usr/local/etc/php/conf.d/ext-cfg.ini
COPY production/dockerfiles/configs/php-fpm/php.ini /usr/local/etc/php/php.ini
RUN docker-php-ext-install pdo_pgsql pgsql
RUN sed -i "s#https#\http#g" /etc/apk/repositories && \
    apk add  --no-cache make icu-dev libpq-dev icu pngquant \
    && curl -O https://www.jonof.id.au/files/kenutils/pngout-20200115-linux-static.tar.gz \
    && tar zxf pngout-20200115-linux-static.tar.gz \
    && cd pngout-20200115-linux-static \
    && cp -f amd64/pngout-static /usr/local/bin/pngout \
    && cd .. && rm -r pngout-20200115-linux-static.tar.gz && rm -r pngout-20200115-linux-static && rm -r .git \
    && apk update

USER www-data
