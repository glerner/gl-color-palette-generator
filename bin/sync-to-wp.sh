#!/bin/bash

# Define paths (use environment variables if set, otherwise use defaults)
PLUGIN_SOURCE=${PLUGIN_SOURCE:-"$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"}
WP_ROOT=${WP_ROOT:-"/home/george/sites/wordpress"}
PLUGIN_DEST="${WP_ROOT}/wp-content/plugins/gl-color-palette-generator"
WP_TESTS_DIR="${WP_ROOT}/wordpress-phpunit"

echo "Using paths:"
echo "Plugin source: $PLUGIN_SOURCE"
echo "WordPress root: $WP_ROOT"
echo "Plugin destination: $PLUGIN_DEST"

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
    "${PLUGIN_SOURCE}/" "${PLUGIN_DEST}/"

# Copy vendor directory separately to preserve symlinks
if [ -d "${PLUGIN_SOURCE}/vendor" ]; then
    echo "Syncing vendor directory..."
    rsync -av --delete "${PLUGIN_SOURCE}/vendor/" "${PLUGIN_DEST}/vendor/"
fi

# Ensure proper permissions
cd "${WP_ROOT}" && lando ssh -c "chown -R www-data:www-data /app/wp-content/plugins/gl-color-palette-generator"
