#!/bin/bash

# Load environment variables from .env.testing if it exists
if [ -f "${PLUGIN_SOURCE}/.env.testing" ]; then
    # Export all variables from .env.testing
    export $(cat "${PLUGIN_SOURCE}/.env.testing" | grep -v '^#' | xargs)
fi

# Define paths (use environment variables if set, otherwise use defaults)
PLUGIN_SOURCE=${PLUGIN_SOURCE:-"$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"}
FILESYSTEM_WP_ROOT=${FILESYSTEM_WP_ROOT:-"/home/george/sites/wordpress"}
PLUGIN_DEST="${FILESYSTEM_WP_ROOT}/wp-content/plugins/gl-color-palette-generator"
WP_TESTS_DIR="${FILESYSTEM_WP_ROOT}/wordpress-phpunit"

echo "Using paths:"
echo "  Plugin source: $PLUGIN_SOURCE"
echo "  WordPress root: $FILESYSTEM_WP_ROOT"
echo "  Plugin destination: $PLUGIN_DEST"

# Check if .lando.example.yml is newer than WordPress .lando.yml
if [ -f "${FILESYSTEM_WP_ROOT}/.lando.yml" ] && [ -f "${PLUGIN_SOURCE}/.lando.example.yml" ]; then
    if [ "${PLUGIN_SOURCE}/.lando.example.yml" -nt "${FILESYSTEM_WP_ROOT}/.lando.yml" ]; then
        echo "⚠️  Warning: .lando.example.yml has been updated."
        echo "   Please review changes and update your .lando.yml:"
        echo "   cp ${PLUGIN_SOURCE}/.lando.example.yml ${FILESYSTEM_WP_ROOT}/.lando.yml"
        echo "   Then run: lando rebuild -y"
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
    --exclude=.env.testing \
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
elif [[ "$PHP_VERSION" =~ ^8\.3\. ]]; then
    COMPOSER_FILE="composer.php83.json"
elif [[ "$PHP_VERSION" =~ ^8\.4\. ]]; then
    COMPOSER_FILE="composer.php83.json"
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

# Handle permissions
if command -v lando >/dev/null 2>&1; then
    echo "Setting permissions using Lando..."
    cd "${FILESYSTEM_WP_ROOT}" && lando ssh -c "chown -R www-data:www-data /app/wp-content/plugins/gl-color-palette-generator"
else
    echo "Please set appropriate permissions for your environment on: $PLUGIN_DEST"
fi

# Return to plugin destination directory
echo -e "Plugin files synced to: $PLUGIN_DEST. \nDone (if all went well).\n"
cd "$PLUGIN_DEST"
