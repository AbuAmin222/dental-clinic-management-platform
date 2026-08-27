# ==========================================
# STAGE 1: COMPILING BACKEND DEPENDENCIES (Composer)
# ==========================================
FROM composer:2.8.6 AS backend-vendor-builder
WORKDIR /app

ENV COMPOSER_HOME=/tmp/composer-home \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts \
    --prefer-dist

# ==========================================
# STAGE 2: COMPILING FRONTEND ASSETS (Vite + Vue3)
# ==========================================
FROM node:20-alpine AS frontend-assets-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
COPY --from=backend-vendor-builder /app/vendor ./vendor
RUN npm run build

# ==========================================
# STAGE 3: PRODUCTION RUNTIME ENVIRONMENT
# ==========================================
FROM php:8.5.9-fpm-alpine
WORKDIR /var/www

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    apk add --no-cache nginx supervisor curl zip unzip git bash && \
    install-php-extensions pdo_mysql gd zip opcache redis

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY . .
COPY --from=backend-vendor-builder /app/vendor ./vendor
COPY --from=frontend-assets-builder /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
