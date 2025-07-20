FROM php:8.2-apache

# 复制所有代码到容器的 Web 目录
COPY . /var/www/html/

RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# 启用 Apache 的 rewrite 模块（如果用到了 .htaccess）
RUN a2enmod rewrite

# 设置上传目录权限（可选）
RUN chmod -R 755 /var/www/html/uploads

EXPOSE 80
