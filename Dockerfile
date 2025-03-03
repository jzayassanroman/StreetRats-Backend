# Usa la imagen oficial de PHP 8.3 CLI
FROM php:8.3-cli

# Instalar dependencias del sistema y extensiones de PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zlib1g-dev \
    libxml2-dev \
    libonig-dev \
    libssl-dev \
    && docker-php-ext-install intl pdo pdo_pgsql pgsql zip opcache

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/symfony

# Copiar archivos del proyecto
COPY . .

# Crear los directorios necesarios antes de asignar permisos
RUN mkdir -p var/cache var/log var/sessions vendor \
    && chmod -R 775 var vendor

# Crear un usuario para ejecutar Symfony
RUN useradd -m symfonyuser \
    && chown -R symfonyuser:symfonyuser /var/www/symfony

# Cambiar al usuario creado
USER symfonyuser

# Instalar dependencias de Symfony sin ejecutar scripts
RUN composer install --no-interaction --optimize-autoloader --no-scripts

# Configurar Symfony para desarrollo
ENV APP_ENV=dev

# Exponer el puerto 8000
EXPOSE 8000

# Mantener el servidor corriendo en segundo plano con supervisord
COPY supervisord.conf /etc/supervisord.conf

# Instalar supervisord
USER root
RUN apt-get install -y supervisor

# Ejecutar supervisord para mantener PHP corriendo
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
