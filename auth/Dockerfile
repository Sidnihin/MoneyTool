FROM php:8.1-cli
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql
WORKDIR /app
COPY auth.php /app
CMD ["php", "-S", "0.0.0.0:8001", "auth.php"]