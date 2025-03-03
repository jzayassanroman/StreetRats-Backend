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

# Crear directorio de trabajo
WORKDIR /var/www/symfony

# Copiar los archivos del proyecto
COPY . .

# Establecer los permisos correctos para los archivos
RUN chown -R www-data:www-data /var/www/symfony

# Cambiar a usuario no-root
USER www-data

# Instalar dependencias de Symfony sin ejecutar los scripts
RUN composer install --no-scripts --no-autoloader

# Crear el directorio var/ manualmente si no existe
RUN mkdir -p var/cache var/logs var/sessions && chmod -R 777 var/

# Configurar Symfony para desarrollo
ENV APP_ENV=dev

# Configurar Volumes para cambios en caliente
VOLUME ["/var/www/symfony"]

# Exponer el puerto 80 (Apache)
EXPOSE 8000

# Configurar Apache para usar el directorio public/ como root
RUN echo "<VirtualHost *:8000>\n\
    DocumentRoot /var/www/symfony/public\n\
    <Directory /var/www/symfony/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Iniciar Apache en primer plano
CMD ["apache2-foreground"]