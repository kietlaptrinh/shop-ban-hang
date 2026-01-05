# Sử dụng PHP 7.4
FROM php:7.4-apache

# Cập nhật hệ thống và cài đặt gói chứng chỉ bảo mật (CA Certificates) - QUAN TRỌNG
RUN apt-get update && apt-get install -y ca-certificates && update-ca-certificates

# Cài đặt extension MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Bật Mod Rewrite
RUN a2enmod rewrite

RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copy code
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html


RUN sed -i 's/SECLEVEL=2/SECLEVEL=1/g' /etc/ssl/openssl.cnf

# Mở cổng 80
EXPOSE 80