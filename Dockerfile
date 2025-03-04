# Usa la imagen oficial de PHP con Apache
FROM php:8.3

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
    && docker-php-ext-install intl pdo pdo_pgsql pgsql zip opcache bcmath sockets

# Instalar Composer manualmente para evitar problemas con la versión
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Crear directorio de trabajo
WORKDIR /var/www/symfony

# Crear un usuario no root para ejecutar Composer
RUN useradd -m symfonyuser

# Copiar los archivos del proyecto
COPY . .

# Establecer los permisos correctos para los archivos
RUN chown -S symfonyuser:symfonyuser /var/www/symfony

# Cambiar a usuario root temporalmente para instalar dependencias
USER root

# Limpiar caché de Composer
RUN composer clear-cache

# Instalar dependencias de Symfony con más detalles en caso de error
RUN composer install --no-scripts --no-interaction --verbose

# Volver a usuario no-root
USER symfonyuser

# Crear el directorio var/ manualmente si no existe
RUN mkdir -p var && chmod -R 777 var/

# Configurar Symfony para desarrollo
ENV APP_ENV=dev

# Configurar Volumes para cambios en caliente
VOLUME ["/var/www/symfony"]

# Exponer el puerto (usando el servidor embebido de PHP)
EXPOSE 8000

# Ejecutar la instalación y levantar el servidor
CMD composer install && php -S 0.0.0.0:8000 -t public