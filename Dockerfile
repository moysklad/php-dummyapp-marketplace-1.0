# Используем официальный образ PHP с Apache
FROM php:8.2-apache

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем расширения PHP
RUN docker-php-ext-install pdo_mysql zip opcache

# Включаем модуль Apache rewrite
RUN a2enmod rewrite

# Копируем файлы приложения
COPY . /var/www/html/

# Настраиваем права (Apache работает от www-data)
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Настраиваем document root (если нужно изменить)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/src/php
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Порт, который будет слушать Apache
EXPOSE 80

# Запускаем Apache в foreground режиме
CMD ["apache2-foreground"]
