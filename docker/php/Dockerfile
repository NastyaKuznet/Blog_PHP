FROM php:8.3.4-fpm-bullseye

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    TZ="Asia/Yekaterinburg" \
    LANG="ru_RU.UTF-8"

RUN apt-get -y update \
    && apt-get -y install locales && echo "ru_RU.UTF-8 UTF-8" >> /etc/locale.gen && locale-gen \
    # Утилиты
    && apt-get install -y git wget mc nano \
    # Установка расширений PHP
    && docker-php-ext-install pdo_mysql mysqli pcntl sockets bcmath iconv \
    && apt-get install -y libpq-dev && docker-php-ext-install pdo_pgsql \
    && apt-get install -y libffi-dev && docker-php-ext-install ffi \
    && apt-get install -y libc-client-dev libkrb5-dev && PHP_OPENSSL=yes docker-php-ext-configure imap --with-kerberos --with-imap-ssl && docker-php-ext-install imap \
    && apt-get install -y libpng-dev libfreetype6-dev libjpeg-dev libzip-dev && docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install gd \
    && apt-get install -y zlib1g-dev && docker-php-ext-install zip \
    && apt-get install -y libonig-dev && docker-php-ext-install mbstring opcache \
    && apt-get install -y libsodium-dev && docker-php-ext-install sodium \
    && pecl install apcu && docker-php-ext-enable apcu \
    && apt-get install -y libmemcached-dev && pecl install memcached && docker-php-ext-enable memcached \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get install -y libicu-dev && docker-php-ext-install intl \
    # php-imagick
    && apt-get install -y libmagickwand-dev build-essential libtool uuid-dev automake autotools-dev libltdl-dev libltdl7 \
    && pecl install imagick \
    && docker-php-ext-enable imagick

RUN apt-get -y clean \
    && apt-get -y autoclean \
    && apt-get -y autoremove \
    && rm -rf /var/lib/apt/lists/*

# Ставим Composer'а.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER www-data:www-data

WORKDIR /var/www/

# Запускаем контейнер
CMD ["php-fpm", "-R"]
