# Base stage - common setup
FROM php:8.3-cli AS base

# Set working directory
WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first (for better layer caching)
COPY composer.json composer.lock* ./

# Production stage
FROM base AS game

# Install PHP dependencies without dev packages
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application source code
COPY . .

# Create a non-root user
RUN groupadd -r appuser && useradd -r -g appuser appuser

# Create the var directory and set proper ownership
RUN mkdir -p /app/var && chown -R appuser:appuser /app

# Create volume for save files
VOLUME ["/app/var"]

USER appuser

# Default command to run the application
CMD ["php", "bin/game.php"]

# Test stage
FROM base AS test

# Install PHP dependencies including dev packages
RUN composer install --optimize-autoloader --no-interaction

# Copy application source code
COPY . .

# Create a non-root user
RUN groupadd -r testuser && useradd -r -g testuser testuser

# Create the var directory and set proper ownership
RUN mkdir -p /app/var && chown -R testuser:testuser /app

USER testuser

# Default command to run tests
CMD ["vendor/bin/phpunit", "tests/"]
