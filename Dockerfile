FROM php:7.4-apache

# Cài đặt extension để kết nối MySQL (mysqli)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Bật tính năng mod_rewrite của Apache (để chạy .htaccess xóa index.php)
RUN a2enmod rewrite

# Copy toàn bộ code vào thư mục web của server
COPY . /var/www/html/

# Cấp quyền ghi file (quan trọng để web không bị lỗi khi upload ảnh)
RUN chown -R www-data:www-data /var/www/html

# Mở cổng 80
EXPOSE 80