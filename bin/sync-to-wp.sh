#!/bin/bash

# Define paths (use environment variables if set, otherwise use defaults)
PLUGIN_SOURCE=${PLUGIN_SOURCE:-"$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"}
WP_ROOT=${WP_ROOT:-"/home/george/sites/wordpress"}
PLUGIN_DEST="${WP_ROOT}/wp-content/plugins/gl-color-palette-generator"
WP_TESTS_DIR="${WP_ROOT}/wordpress-phpunit"

echo "Using paths:"
echo "  Plugin source: $PLUGIN_SOURCE"
echo "  WordPress root: $WP_ROOT"
echo "  Plugin destination: $PLUGIN_DEST"

# Check if .lando.example.yml is newer than WordPress .lando.yml
if [ -f "${WP_ROOT}/.lando.yml" ] && [ -f "${PLUGIN_SOURCE}/.lando.example.yml" ]; then
    if [ "${PLUGIN_SOURCE}/.lando.example.yml" -nt "${WP_ROOT}/.lando.yml" ]; then
        echo "⚠️  Warning: .lando.example.yml has been updated."
        echo "   Please review changes and update your .lando.yml:"
        echo "   cp ${PLUGIN_SOURCE}/.lando.example.yml ${WP_ROOT}/.lando.yml"
        echo ""
    fi
fi

# Ensure vendor directory exists in source
if [ ! -d "${PLUGIN_SOURCE}/vendor" ]; then
    echo "Installing composer dependencies in source..."
    cd "${PLUGIN_SOURCE}" && composer install
fi

# Sync plugin files to WordPress plugins directory
rsync -av --delete \
    --exclude=.git/ \
    --exclude=.gitignore \
    --exclude=.env \
    --exclude=node_modules/ \
    --exclude=vendor/ \
    --exclude=.lando/ \
    --exclude=.lando.yml \
    --exclude=.lando.local.yml \
    --exclude=wp-tests-config.php \
    --exclude=tests/phpunit/ \
    "${PLUGIN_SOURCE}/" "${PLUGIN_DEST}/"

# Copy vendor directory separately to preserve symlinks
if [ -d "${PLUGIN_SOURCE}/vendor" ]; then
    echo "Syncing vendor directory..."
    rsync -av --delete "${PLUGIN_SOURCE}/vendor/" "${PLUGIN_DEST}/vendor/"
fi

# Detect PHP version and use appropriate composer file
PHP_VERSION=$(php -r "echo PHP_VERSION;")
if [[ "$PHP_VERSION" =~ ^8\.1\. ]]; then
    COMPOSER_FILE="composer.php81.json"
elif [[ "$PHP_VERSION" =~ ^8\.2\. ]]; then
    COMPOSER_FILE="composer.php82.json"
else
    echo "Unsupported PHP version: $PHP_VERSION"
    exit 1
fi

echo "Detected PHP version: $PHP_VERSION"
echo "Using composer file: $COMPOSER_FILE"

# Run composer dump-autoload in the destination directory
cd "$PLUGIN_DEST" || exit 1
echo "Regenerating autoloader files..."
composer dump-autoload

# Ensure proper permissions
if command -v lando >/dev/null 2>&1; then
    echo "Setting permissions using Lando..."
    cd "${WP_ROOT}" && lando ssh -c "chown -R www-data:www-data /app/wp-content/plugins/gl-color-palette-generator"
else
    echo "Setting permissions for local environment..."
    # Try to get web server user
    if [ -f /etc/apache2/envvars ]; then
        # Apache
        WEB_USER=$(. /etc/apache2/envvars && echo $APACHE_RUN_USER)
        WEB_GROUP=$(. /etc/apache2/envvars && echo $APACHE_RUN_GROUP)
    elif [ -f /etc/nginx/nginx.conf ]; then
        # Nginx
        WEB_USER=$(grep -r "user[[:space:]]" /etc/nginx/nginx.conf | awk '{print $2}' | sed 's/;//')
        WEB_GROUP=$WEB_USER
    else
        # Fallback to common defaults
        WEB_USER="www-data"
        WEB_GROUP="www-data"
    fi

    # Set permissions
    if [ "$(id -u)" -eq 0 ]; then
        # If running as root
        chown -R "$WEB_USER:$WEB_GROUP" "$PLUGIN_DEST"
    else
        # If not root, try sudo if available
        if command -v sudo >/dev/null 2>&1; then
            echo "Requesting sudo access to set permissions..."
            sudo chown -R "$WEB_USER:$WEB_GROUP" "$PLUGIN_DEST"
        else
            echo "Warning: Could not set permissions. Please ensure your web server has proper access to: $PLUGIN_DEST"
        fi
    fi
fi
