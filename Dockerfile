FROM php:7.4-apache

# Use the official Docker PHP extension installer to avoid Debian mirror 404 issues
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install required PHP extensions (do NOT include intl because it conflicts with ASTPP's custom Locale class)
RUN install-php-extensions gd mysqli pdo_mysql mbstring xml zip gettext bcmath curl

# Enable Apache mod_rewrite for CodeIgniter URLs
RUN a2enmod rewrite

# Configure Apache DocumentRoot and AllowOverride for .htaccess
RUN sed -i 's#AllowOverride None#AllowOverride All#g' /etc/apache2/apache2.conf

# Set PHP settings required by ASTPP & CodeIgniter
RUN echo "short_open_tag = On" >> /usr/local/etc/php/conf.d/astpp.ini && \
    echo "output_buffering = 4096" >> /usr/local/etc/php/conf.d/astpp.ini && \
    echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/astpp.ini && \
    echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/astpp.ini && \
    echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/astpp.ini && \
    echo "date.timezone = UTC" >> /usr/local/etc/php/conf.d/astpp.ini

# Create configuration directory expected by ASTPP
RUN mkdir -p /var/lib/astpp

WORKDIR /var/www/html
