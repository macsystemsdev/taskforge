# ============================================================
# Stage 1 — PHP runtime base
# ============================================================

FROM php:8.4-cli AS php-base

WORKDIR /var/www/html


# ------------------------------------------------------------
# OS dependencies
# ------------------------------------------------------------

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
        git \
    && rm -rf /var/lib/apt/lists/*


# ------------------------------------------------------------
# PHP extensions
# ------------------------------------------------------------

RUN docker-php-ext-install \
    bcmath \
    intl \
    mbstring \
    pcntl \
    pdo_mysql \
    xml \
    zip


# Redis PHP extension
RUN pecl install redis \
    && docker-php-ext-enable redis


# ============================================================
# Stage 2 — PHP dependencies
# ============================================================

FROM php-base AS dependencies

# Copy Composer from official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Dependency manifests first for layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies without running Laravel scripts
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts


# ============================================================
# Stage 3 — Frontend build
# ============================================================
FROM node:22 AS frontend

WORKDIR /app


# ------------------------------------------------------------
# Build-time frontend configuration
# ------------------------------------------------------------

ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME

ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY
ENV VITE_REVERB_HOST=$VITE_REVERB_HOST
ENV VITE_REVERB_PORT=$VITE_REVERB_PORT
ENV VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME


# ------------------------------------------------------------
# Node dependencies
# ------------------------------------------------------------

COPY package*.json ./

RUN npm ci


# ------------------------------------------------------------
# Application source
# ------------------------------------------------------------

COPY . .


# ------------------------------------------------------------
# Laravel Composer dependencies
# ------------------------------------------------------------

COPY --from=dependencies /var/www/html/vendor ./vendor


# ------------------------------------------------------------
# Build production assets
# ------------------------------------------------------------

RUN npm run build


# ============================================================
# Stage 4 — Final Laravel runtime
# ============================================================

FROM php-base

WORKDIR /var/www/html


# ------------------------------------------------------------
# Composer
# ------------------------------------------------------------

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# ------------------------------------------------------------
# PHP dependencies
# ------------------------------------------------------------

COPY --from=dependencies /var/www/html/vendor ./vendor


# ------------------------------------------------------------
# Application source
# ------------------------------------------------------------

COPY . .


# ------------------------------------------------------------
# Compiled frontend assets
# ------------------------------------------------------------

COPY --from=frontend /app/public/build ./public/build



# ------------------------------------------------------------
# Laravel writable directories
# ------------------------------------------------------------

RUN chown -R www-data:www-data \
        storage \
        bootstrap/cache