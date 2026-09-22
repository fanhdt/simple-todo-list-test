FROM php:8.2-cli

# Install dependency sistem yang dibutuhkan ekstensi PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

# Render menyediakan variable PORT secara otomatis.
# Untuk local development, default ke 8000.
ENV PORT=8000

EXPOSE 8000

# Gunakan built-in PHP server, listen di 0.0.0.0 agar
# bisa diakses dari luar container (wajib untuk Render).
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public"]
