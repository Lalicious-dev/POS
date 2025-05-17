# Usa una imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instala dependencias necesarias para Laravel
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libonig-dev libpq-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring gd exif pcntl bcmath sockets

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia el código al contenedor
COPY . /var/www/html

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Da permisos al storage y bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Instala las dependencias de PHP con Composer
RUN composer install --no-dev --optimize-autoloader

# Expone el puerto 80 para el tráfico HTTP
EXPOSE 80

# Comando para iniciar Apache en primer plano
CMD ["apache2-foreground"]