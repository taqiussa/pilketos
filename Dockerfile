# Stage: Production (Apache)
FROM php:8.4-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies (minimal for Laravel)
RUN apt-get update -y && \
    apt-get install -y --no-install-recommends \
    curl \
    git \
    unzip \
    zip \
    nano \
    g++ \
    make \
    pkg-config \
    autoconf \
    build-essential \
    ca-certificates \
    libicu-dev \
    libxml2-dev \
    libxslt1-dev \
    libzip-dev \
    libpng-dev \
    libsodium-dev \
    libpq-dev \
    libldap2-dev \
    libonig-dev \
    ffmpeg \
    wget \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# Configure and install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    mbstring \
    xml \
    bcmath \
    opcache \
    zip \
    fileinfo \
    pcntl \
    intl \
    soap \
    xsl \
    exif \
    sockets \
    pdo_pgsql \
    ldap


# Remove PECL extensions (imagick, memcached, redis) — NOTHING INSTALLED

# Set Apache Document Root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Optimize PHP settings + OPcache for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    { \
      echo "upload_max_filesize = 30M"; \
      echo "post_max_size = 30M"; \
      echo "max_execution_time = 600"; \
      echo "memory_limit = 4096M"; \
      echo ""; \
      echo "; OPcache settings"; \
      echo "opcache.enable=1"; \
      echo "opcache.memory_consumption=256"; \
      echo "opcache.interned_strings_buffer=8"; \
      echo "opcache.max_accelerated_files=4000"; \
      echo "opcache.revalidate_freq=2"; \
      echo "opcache.fast_shutdown=1"; \
      echo "opcache.validate_timestamps=1"; \
    } >> "$PHP_INI_DIR/php.ini"

# Create Laravel directories and set permissions
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Add healthcheck script
COPY ./healthcheck.sh /usr/local/bin/healthcheck.sh
RUN chmod +x /usr/local/bin/healthcheck.sh

# Expose port
EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD /usr/local/bin/healthcheck.sh || exit 1
