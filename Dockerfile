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

# Crear usuario y grupo 'symfonyuser' para mayor seguridad
RUN useradd -m -d /home/symfonyuser -s /bin/bash symfonyuser

# Crear directorio de trabajo
WORKDIR /var/www/symfony

# Copiar los archivos del proyecto
COPY . .

# Crear los directorios necesarios antes de cambiar permisos
RUN mkdir -p var/cache var/logs var/sessions config/jwt /var/log/supervisor /var/log/symfony && \
    chown -R symfonyuser:symfonyuser /var/www/symfony && \
    chmod -R 775 /var/www/symfony/var && \
    chmod -R 775 /var/log/symfony && \
    [ -d /var/www/symfony/vendor ] && chmod -R 775 /var/www/symfony/vendor || echo "El directorio vendor no existe"

# Copiar configuración de Supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Cambiar a usuario symfonyuser para mayor seguridad
USER symfonyuser

# Instalar dependencias de Symfony con permisos correctos
RUN composer install --no-interaction --optimize-autoloader

# Generar claves JWT para autenticación (si no existen)
RUN if [ ! -f config/jwt/private.pem ]; then \
    openssl genpkey -algorithm RSA -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:4096 && \
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem && \
    chown -R symfonyuser:symfonyuser config/jwt && \
    chmod 600 config/jwt/private.pem && chmod 644 config/jwt/public.pem; \
    fi

# Exponer el puerto 8000 para el servidor Symfony
EXPOSE 8000

# Comando de inicio: ejecutar Supervisor
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
