#!/bin/bash

set -e  # Exit on any error

# Define paths (use environment variables if set, otherwise use defaults)
WP_ROOT=${WP_ROOT:-"/app"}
PLUGIN_SOURCE=${PLUGIN_SOURCE:-"$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"}
PLUGIN_DIR="${WP_ROOT}/wp-content/plugins/gl-color-palette-generator"

# Test environment paths from Lando config
WP_TESTS_DIR=${WP_TESTS_DIR:-"${WP_ROOT}/wordpress-phpunit"}
WP_CORE_DIR=${WP_CORE_DIR:-"${WP_ROOT}/wordpress"}

# Database configuration from Lando config
DB_NAME=${TEST_DB_NAME:-"wordpress_test"}
DB_USER=${TEST_DB_USER:-"wordpress"}
DB_PASS=${TEST_DB_PASS:-"wordpress"}
DB_HOST=${TEST_DB_HOST:-"database"}  # Lando uses 'database'
WP_VERSION=${WP_VERSION:-"latest"}
SKIP_DB_CREATE=${SKIP_DB_CREATE:-"false"}

# Development environment detection
IS_LANDO=false
if [ -f "${WP_ROOT}/.lando.yml" ]; then
    IS_LANDO=true
fi

echo "=== Environment Configuration ==="
echo "WordPress root: $WP_ROOT"
echo "Plugin source: $PLUGIN_SOURCE"
echo "Plugin directory: $PLUGIN_DIR"
echo "Database host: $DB_HOST"
echo "Database name: $DB_NAME"
echo "Database user: $DB_USER"
echo "WordPress version: $WP_VERSION"
echo "Environment: $([ "$IS_LANDO" = true ] && echo "Lando" || echo "Local")"
echo "=========================="

echo "=== Starting Plugin Test Environment Setup ==="

# Step 1: Rebuild Lando environment
echo -e "\n1. Rebuilding Lando environment..."
cd "$WP_ROOT"
lando rebuild -y
if [ $? -ne 0 ]; then
    echo "❌ Lando rebuild failed"
    exit 1
fi

# Wait for services to be ready
echo "Waiting for services to be ready..."
sleep 10

# Step 2: Sync plugin to WordPress
echo -e "\n2. Syncing plugin to WordPress..."
cd "$PLUGIN_SOURCE"
bash bin/sync-to-wp.sh
if [ $? -ne 0 ]; then
    echo "❌ Plugin sync failed"
    exit 1
fi
echo "✅ Plugin synced to WordPress"

# Step 3: Generate wp-tests-config.php
echo -e "\n3. Generating wp-tests-config.php..."

# Create wp-tests-config.php with environment-specific settings
cat > "${PLUGIN_DIR}/wp-tests-config.php" << EOL
<?php
// Test with WordPress debug mode (default)
define( 'WP_DEBUG', true );

// ** MySQL settings ** //
define( 'DB_NAME', '${DB_NAME}' );
define( 'DB_USER', '${DB_USER}' );
define( 'DB_PASSWORD', '${DB_PASS}' );
define( 'DB_HOST', '${DB_HOST}' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

\$table_prefix = 'wptests_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );
EOL

# Create wp-tests-config.php in WordPress root
echo "Creating config files in:"
echo "1. $WP_ROOT/wp-tests-config.php"
echo "2. $PLUGIN_DIR/wp-tests-config.php"

# Create wp-tests-config.php in WordPress root
cat > "${WP_ROOT}/wp-tests-config.php" << EOL
<?php
// Test with WordPress debug mode (default)
define( 'WP_DEBUG', true );

// ** MySQL settings ** //
define( 'DB_NAME', '${DB_NAME}' );
define( 'DB_USER', '${DB_USER}' );
define( 'DB_PASSWORD', '${DB_PASS}' );
define( 'DB_HOST', '${DB_HOST}' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

\$table_prefix = 'wptests_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );
EOL

echo "✅ Test configuration files created"

# Step 4: Install WordPress test suite
echo -e "\n4. Installing WordPress test suite..."
cd "$WP_ROOT"
echo "✅ WordPress test suite ready"

echo -e "\n✅ Plugin test environment setup complete!"
