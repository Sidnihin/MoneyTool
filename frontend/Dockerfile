FROM php:8.1-apache

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    curl \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Установка рабочей директории
WORKDIR /var/www/html

# Копируем только composer.json и устанавливаем зависимости
COPY composer.json /var/www/html/composer.json

RUN composer install --no-interaction

# Копируем все необходимые файлы из вашего проекта
COPY . /var/www/html/

# Открываем порт
EXPOSE 80

# Запуск Apache
CMD ["apache2-foreground"]