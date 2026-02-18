
# Dockerfile para app PHP (Apache)
FROM php:8.1-apache

# Evitar preguntas interactivas
ARG DEBIAN_FRONTEND=noninteractive

# Instalar utilidades y extensiones que suelen necesitarse
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libicu-dev \
    libpq-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql pdo_pgsql mysqli gd zip intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Habilitar rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

# Copiar todo el código
COPY . /var/www/html

# Instalar dependencias si el proyecto usa composer (si no, este paso falla y lo podés eliminar)
# RUN composer install --no-dev --no-interaction --optimize-autoloader

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Puerto por defecto (Render detecta 80)
EXPOSE 80

CMD ["apache2-foreground"]
