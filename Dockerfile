FROM php:7.4-apache AS base

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN apt-get update && apt-get install -y \
        libicu-dev \
        zlib1g-dev \
        unzip \
        iputils-ping \
        net-tools \
        acl \
        libzip-dev \
        libmemcached-tools \
    && chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions \
        zip \
        intl \
    && pecl install xdebug \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY resources/php/zzz-dev.ini /usr/local/etc/php/conf.d/zzz-dev.ini
COPY resources/apache/app.conf /etc/apache2/sites-available/app.conf

RUN a2enmod rewrite negotiation \
    && a2dissite 000-default.conf \
    && a2ensite app.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer

ENV PATH="/app/bin:/app/vendor/bin:${PATH}"

############### DEVELOPMENT ###############
FROM base AS development

ENV APP_ENV=dev

ADD . /app

COPY ./resources/docker/entrypoint.sh /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

############### PRE-PRODUCTION ###############
FROM development AS preprod

ENV APP_ENV=prod

RUN rm -f /usr/local/etc/php/conf.d/zzz-dev.ini && \
    mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini && \
    rm -fr /app/var/cache/* && \
    composer install --no-dev --optimize-autoloader --no-interaction --classmap-authoritative && \
    composer clearcache && \
    composer config --global --unset github-oauth.github.com

RUN chown -R www-data:www-data var && \
    chown -R www-data:www-data var && \
    setfacl -R -m u:"www-data":rwX var && \
    setfacl -dR -m u:"www-data":rwX var

############### PRODUCTION ###############
FROM preprod AS production

CMD ["apache2-foreground"]
