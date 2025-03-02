# Usa la imagen oficial de PHP con Apache
FROM php:8.3-apache

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

# Configurar Apache para que el DocumentRoot sea el directorio público de Symfony
RUN sed -i 's!/var/www/html!/var/www/symfony/public!g' /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite

# Crear directorio de trabajo
WORKDIR /var/www/symfony

# Copiar los archivos del proyecto
COPY . .

# Asegurar que los permisos sean correctos para Apache y Symfony
RUN chown -R www-data:www-data /var/www/symfony \
    && chmod -R 775 /var/www/symfony/var

# Cambiar a usuario no-root para mayor seguridad
USER www-data

# Instalar dependencias de Symfony sin ejecutar scripts para evitar problemas
RUN composer install --no-scripts --no-autoloader --no-interaction --optimize-autoloader

# Crear el directorio var/ manualmente si no existe
RUN mkdir -p var/cache var/logs var/sessions && chmod -R 777 var/

# Configurar Symfony para desarrollo
ENV APP_ENV=dev

# Exponer el puerto 80 para Apache
EXPOSE 8000

# Comando de inicio que instala dependencias y arranca Apache
CMD ["sh", "-c", "composer install --no-interaction && apache2-foreground"]
