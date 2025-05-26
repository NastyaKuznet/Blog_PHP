FROM php:8.3.4-fpm-bullseye

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    TZ="Asia/Yekaterinburg" \
    LANG="ru_RU.UTF-8"

RUN apt-get -y update \
    && apt-get -y install -y locales git wget mc nano \
    && echo "ru_RU.UTF-8 UTF-8" >> /etc/locale.gen && locale-gen \
    && apt-get install -y libpq-dev \
    && docker-php-ext-install pcntl sockets bcmath iconv pdo_pgsql \
    && apt-get clean \
    && apt-get autoclean \
    && apt-get autoremove \
    && rm -rf /var/lib/apt/lists/*

# Ставим Composer'а.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER www-data:www-data

WORKDIR /var/www/

# Запускаем контейнер
CMD ["php-fpm", "-R"]
