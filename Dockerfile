# Usa la imagen oficial de PHP 8.3 (CLI)
FROM php:8.3-cli

# Instalar dependencias del sistema y extensiones de PHP necesarias
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
    supervisor \
    && docker-php-ext-install intl pdo pdo_pgsql pgsql zip opcache

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear directorio de trabajo
WORKDIR /var/www/symfony

# Copiar los archivos del proyecto
COPY . .

# Crear los directorios necesarios antes de cambiar permisos
RUN mkdir -p var/cache var/logs var/sessions config/jwt /var/log/supervisor && \
    chown -R www-data:www-data /var/www/symfony && \
    chmod -R 775 /var/www/symfony/var && \
    chmod -R 775 /var/www/symfony/vendor

# Copiar configuración de Supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Cambiar a usuario no-root para mayor seguridad
USER www-data

# Instalar dependencias de Symfony con permisos correctos
RUN composer install --no-interaction --optimize-autoloader

# Generar claves JWT para autenticación (si no existen)
RUN if [ ! -f config/jwt/private.pem ]; then \
    openssl genpkey -algorithm RSA -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:4096 && \
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem && \
    chown -R www-data:www-data config/jwt && \
    chmod 600 config/jwt/private.pem && chmod 644 config/jwt/public.pem; \
    fi

# Exponer el puerto 8000 para el servidor Symfony
EXPOSE 8000

# Comando de inicio: ejecutar Supervisor
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
