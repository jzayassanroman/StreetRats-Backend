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

# Crear un usuario no-root para mayor seguridad
RUN useradd -m -d /home/symfonyuser -s /bin/bash symfonyuser

# Crear directorio de trabajo
WORKDIR /var/www/symfony

# Copiar los archivos del proyecto
COPY . .

# Establecer permisos adecuados (sin usar 777, que es inseguro)
RUN mkdir -p var/cache var/logs var/sessions config/jwt /var/log/symfony && \
    chown -R symfonyuser:symfonyuser /var/www/symfony && \
    chmod -R 775 /var/www/symfony/var /var/log/symfony

# Cambiar a usuario no-root antes de instalar dependencias
USER symfonyuser

# Limpiar caché de Composer antes de instalar dependencias
RUN composer clear-cache

# Instalar dependencias de Symfony con más detalles en caso de error
RUN composer install --no-interaction --optimize-autoloader --verbose

# Generar claves JWT para autenticación (si no existen)
RUN if [ ! -f config/jwt/private.pem ]; then \
    openssl genpkey -algorithm RSA -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:4096 && \
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem && \
    chown -R symfonyuser:symfonyuser config/jwt && \
    chmod 600 config/jwt/private.pem && chmod 644 config/jwt/public.pem; \
    fi

# Configurar Symfony para desarrollo
ENV APP_ENV=dev

# Configurar Volumes para cambios en caliente
VOLUME ["/var/www/symfony"]

# Exponer el puerto para el servidor embebido de Symfony
EXPOSE 8000

# Ejecutar la instalación y levantar el servidor
CMD composer install && php -S 0.0.0.0:8000 -t public
