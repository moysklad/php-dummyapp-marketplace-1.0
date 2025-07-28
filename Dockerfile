# Используем официальный образ PHP с Apache
FROM php:8.2-apache

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем расширения PHP
RUN docker-php-ext-install zip opcache curl

# Включаем модуль Apache rewrite
#RUN a2enmod rewrite

# Копируем файлы приложения
COPY . /var/www/html/

# Настраиваем права
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Настраиваем document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/src/php
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Установка конфигурационных параметров решения: замените на свои значения
# и не забудьте указать SECRET_KEY при запуске (но не храните его в системе контроля версий)
ENV APP_ID=195d5446-9da8-47ee-abb9-e808e4f283d7
ENV APP_UID=php-demo-app.moysklad
ENV APP_BASE_URL=https://php-demo.testms-test.lognex.ru

# Порт, который будет слушать Apache
EXPOSE 80

# Запускаем Apache в foreground режиме
CMD ["apache2-foreground"]
