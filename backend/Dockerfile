FROM php:8.2-cli


RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

ENV PORT=8000

EXPOSE 8000


CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public"]
