FROM php:8.1-cli

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    curl \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Рабочая директория
WORKDIR /app

# Копирование исходного кода
COPY auth.php /app

# Установка библиотеки firebase/php-jwt через Composer
RUN composer require firebase/php-jwt

# Открытие порта и запуск PHP сервера
CMD ["php", "-S", "0.0.0.0:8001", "auth.php"]