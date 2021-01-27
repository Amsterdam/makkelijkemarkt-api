FROM php:7.3-fpm-alpine

ARG DEBIAN_FRONTEND=noninteractive

EXPOSE 8080

RUN apk update && apk upgrade

RUN apk add bash

RUN apk add nginx && mkdir /run/nginx

RUN apk add postgresql-dev bzip2-dev freetype libpng libjpeg-turbo freetype-dev libpng-dev jpeg-dev libjpeg libjpeg-turbo-dev libintl gettext gettext-dev gmp gmp-dev icu-dev libxml2-dev libxslt-dev libzip libzip-dev && \
    docker-php-ext-configure gd --with-freetype-dir=/usr/lib/ --with-png-dir=/usr/lib/ --with-jpeg-dir=/usr/lib/ --with-gd && \
    docker-php-ext-install pdo_pgsql pgsql bcmath bz2 calendar exif gd gettext gmp intl pcntl shmop soap sockets sysvmsg sysvsem sysvshm wddx xmlrpc xsl zip

COPY . /app

COPY Docker/docker-entrypoint.sh /app/docker-entrypoint.sh

COPY Docker/import-mercato.sh /app/import-mercato.sh

COPY Docker/report.sh /app/report.sh

COPY Docker/nginx/ /etc/nginx/

COPY Docker/php/ /usr/local/etc/php/

WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- -1 && php composer.phar install --prefer-dist --no-scripts

RUN mkdir -p /app/var/cache \
    mkdir -p /app/var/logs \
    mkdir -p /app/public/media \
    && chown -R www-data:www-data /app/var/cache \
    && chmod 770 /app/var/cache \
    && chown -R www-data:www-data /app/var/logs \
    && chmod 770 /app/var/logs \
    && chown -R www-data:www-data /app/public/media \
    && chmod 770 /app/public/media \
    && chmod 775 /app/docker-entrypoint.sh \
    && chmod 775 /app/import-mercato.sh \
    && chmod 775 /app/report.sh

CMD /app/docker-entrypoint.sh
