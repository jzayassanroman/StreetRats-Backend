# Usa la imagen oficial de PHP 8.3
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

# Ajustar permisos correctamente
RUN chmod -R 775 /var/www/symfony/var \
    && chmod -R 775 /var/www/symfony/vendor

# Crear un usuario para ejecutar el servidor
RUN useradd -m symfonyuser

# Asignar permisos al usuario creado
RUN chown -R symfonyuser:symfonyuser /var/www/symfony

# Cambiar al usuario creado
USER symfonyuser

# Instalar dependencias de Symfony sin ejecutar scripts
RUN composer install --no-interaction --optimize-autoloader --no-scripts

# Asegurar que los directorios existen y tienen permisos correctos
RUN mkdir -p var/cache var/log var/sessions && chmod -R 777 var/

# Configurar Symfony para desarrollo
ENV APP_ENV=dev

# Exponer el puerto 8000 para el servidor embebido de Symfony
EXPOSE 8000

# Comando de inicio: mantener el servidor corriendo en segundo plano
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
