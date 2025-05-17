FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip zip libpng-dev libjpeg-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring gd

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . /var/www

RUN composer install

RUN rm -rf /var/www/html && ln -s /var/www/public /var/www/html

RUN chown -R www-data:www-data /var/www

EXPOSE 80
