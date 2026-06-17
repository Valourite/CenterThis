#!/bin/bash
set -euo pipefail

APP_DIR="/home/centerthisco/repositories/CenterThis"
PUBLIC_DIR="/home/centerthisco/public_html"
PHP_BIN="/usr/local/bin/php"
COMPOSER="/home/centerthisco/composer.phar"
BRANCH="main"

LOG_FILE="$APP_DIR/storage/logs/deploy.log"
LOCK_FILE="/tmp/centerthisco-deploy.lock"

# Required when running Composer from a web-triggered process.
export HOME="/home/centerthisco"
export COMPOSER_HOME="/home/centerthisco/.composer"
export COMPOSER_CACHE_DIR="/home/centerthisco/.composer/cache"

mkdir -p "$APP_DIR/storage/logs"
mkdir -p "$COMPOSER_HOME"
mkdir -p "$COMPOSER_CACHE_DIR"

bring_app_up() {
    cd "$APP_DIR" || exit 1
    echo "Bringing app back online..."
    $PHP_BIN artisan up || true
}

(
    flock -n 9 || {
        echo "Another deployment is already running."
        exit 1
    }

    # If anything fails after maintenance mode starts, this ensures the site is not left down.
    trap bring_app_up EXIT

    echo ""
    echo "=================================================="
    echo "Deployment started: $(date)"
    echo "=================================================="

    cd "$APP_DIR"

    echo "Putting app into maintenance mode..."
    $PHP_BIN artisan down || true

    echo "Fetching latest code..."
    git fetch origin "$BRANCH"

    echo "Resetting working tree to origin/$BRANCH..."
    git reset --hard "origin/$BRANCH"

    echo "Installing Composer dependencies..."
    $PHP_BIN "$COMPOSER" install --no-dev --prefer-dist --optimize-autoloader --no-interaction

    echo "Copying public build assets..."
    rm -rf "$PUBLIC_DIR/build"
    cp -R "$APP_DIR/public/build" "$PUBLIC_DIR/build"

    if [ -d "$APP_DIR/public/images" ]; then
        echo "Copying public images..."
        rm -rf "$PUBLIC_DIR/images"
        cp -R "$APP_DIR/public/images" "$PUBLIC_DIR/images"
    fi

    echo "Copying SEO discovery files..."
    if [ -f "$APP_DIR/public/robots.txt"]; then
        cp "$APP_DIR/public/robots.txt" "$PUBLIC_DIR/robots.txt"
    fi

    if [ -f "$APP_DIR/public/sitemap.xml"]; then
        cp "$APP_DIR/public/sitemap.xml" "$PUBLIC_DIR/sitemap.xml"
    fi

    echo "Fixing permissions..."
    chmod -R 775 "$APP_DIR/storage" || true
    chmod -R 775 "$APP_DIR/bootstrap/cache" || true
    chmod -R 755 "$PUBLIC_DIR/build" || true
    chmod 644 "$PUBLIC_DIR/robots.txt" "$PUBLIC_DIR/sitemap.xml" || true

    if [ -d "$PUBLIC_DIR/images" ]; then
        chmod -R 755 "$PUBLIC_DIR/images" || true
    fi

    echo "Clearing Laravel caches..."
    $PHP_BIN artisan optimize:clear

    echo "Running database migrations..."
    $PHP_BIN artisan migrate --force

    echo "Rebuilding Laravel caches..."
    $PHP_BIN artisan optimize

    echo "Deployment completed: $(date)"
    echo "=================================================="

) 9>"$LOCK_FILE" >> "$LOG_FILE" 2>&1