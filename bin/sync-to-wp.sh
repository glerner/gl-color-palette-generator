#!/bin/bash

# Define paths
PLUGIN_SOURCE="/home/george/sites/gl-color-palette-generator"
WP_ROOT="/home/george/sites/wordpress"
PLUGIN_DEST="${WP_ROOT}/wp-content/plugins/gl-color-palette-generator"
WP_TESTS_DIR="${WP_ROOT}/wordpress-phpunit"

# Check if .lando.example.yml is newer than WordPress .lando.yml
if [ -f "${WP_ROOT}/.lando.yml" ] && [ -f "${PLUGIN_SOURCE}/.lando.example.yml" ]; then
    if [ "${PLUGIN_SOURCE}/.lando.example.yml" -nt "${WP_ROOT}/.lando.yml" ]; then
        echo "⚠️  Warning: .lando.example.yml has been updated."
        echo "   Please review changes and update your .lando.yml:"
        echo "   cp ${PLUGIN_SOURCE}/.lando.example.yml ${WP_ROOT}/.lando.yml"
        echo ""
    fi
fi

# Sync plugin files to WordPress plugins directory
rsync -av --delete \
    --exclude=.git/ \
    --exclude=.github/ \
    --exclude=vendor/ \
    --exclude=.lando.yml \
    "${PLUGIN_SOURCE}/" \
    "${PLUGIN_DEST}/"

# Make scripts executable
chmod +x "${PLUGIN_DEST}/bin/"*.sh

# Copy test config to WordPress test directory
cp "${PLUGIN_DEST}/tests/wp-tests-config.php" \
   "${WP_TESTS_DIR}/wp-tests-config.php"

# Copy phpunit.xml to WordPress root and core directories
cp "${PLUGIN_DEST}/phpunit.xml" "${WP_ROOT}/phpunit.xml"
mkdir -p "${WP_ROOT}/core"
cp "${PLUGIN_DEST}/phpunit.xml" "${WP_ROOT}/core/phpunit.xml.dist"
