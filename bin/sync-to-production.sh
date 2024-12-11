#!/bin/bash

# Exit if any command fails
set -e

# Check if target directory is provided
if [ -z "$1" ]; then
    echo "Usage: $0 <target-directory>"
    echo "Example: $0 ~/plugin-releases/gl-color-palette-generator"
    exit 1
fi

PLUGIN_SOURCE="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_DEST="$1"

# Create destination directory if it doesn't exist
mkdir -p "${PLUGIN_DEST}"

# Sync plugin files to production directory
rsync -av --delete \
    --exclude=.git/ \
    --exclude=.gitignore \
    --exclude=.env \
    --exclude=node_modules/ \
    --exclude=vendor/ \
    --exclude=tests/ \
    --exclude=docs/ \
    --exclude=.editorconfig \
    --exclude=.eslintrc.js \
    --exclude=.prettierrc \
    --exclude=jest.config.js \
    --exclude=jest.setup.ts \
    --exclude=tsconfig*.json \
    --exclude=phpunit*.xml \
    --exclude=CONTRIBUTING.md \
    --exclude=CODE_OF_CONDUCT.md \
    --exclude=SECURITY.md \
    --exclude=.lando* \
    --exclude=bin/install-wp-tests*.sh \
    --exclude=bin/setup-plugin-tests.sh \
    --exclude=bin/sync-to-*.sh \
    "${PLUGIN_SOURCE}/" "${PLUGIN_DEST}/"

# Install production dependencies
if [ -f "${PLUGIN_SOURCE}/composer.json" ]; then
    echo "Installing composer dependencies..."
    cd "${PLUGIN_DEST}"
    composer install --no-dev --optimize-autoloader
fi

if [ -f "${PLUGIN_SOURCE}/package.json" ]; then
    echo "Installing npm dependencies..."
    cd "${PLUGIN_DEST}"
    npm ci --production
fi

echo "Production copy created at: ${PLUGIN_DEST}"
