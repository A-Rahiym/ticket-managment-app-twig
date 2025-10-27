# Use official PHP 8.1 image with Apache
FROM php:8.1-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Create directories and set permissions
RUN mkdir -p data data/sessions cache \
    && chmod -R 755 data data/sessions cache \
    && chown -R www-data:www-data data data/sessions cache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Configure Apache to use .htaccess
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>' > /etc/apache2/sites-available/000-default.conf

# Copy .htaccess (if not already in repository)
RUN echo "RewriteEngine On\n\
    RewriteCond %{REQUEST_FILENAME} !-f\n\
    RewriteCond %{REQUEST_FILENAME} !-d\n\
    RewriteRule ^ index.php [QSA,L]" > .htaccess

# Expose port (Render will override with $PORT)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]