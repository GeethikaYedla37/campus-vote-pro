FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite headers

WORKDIR /var/www/html

COPY . /var/www/html
COPY docker/apache-entrypoint.sh /usr/local/bin/campusvote-apache-entrypoint

RUN chown -R www-data:www-data /var/www/html/uploads \
    && chmod +x /usr/local/bin/campusvote-apache-entrypoint \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

ENV PORT=10000

EXPOSE 10000

CMD ["campusvote-apache-entrypoint"]
