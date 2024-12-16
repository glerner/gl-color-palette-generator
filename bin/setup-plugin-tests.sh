#!/bin/bash

set -e  # Exit on any error

# Define paths (use environment variables if set, otherwise use defaults)
WP_ROOT=${WP_ROOT:-"/app"}
PLUGIN_SOURCE=${PLUGIN_SOURCE:-"$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"}
PLUGIN_DIR="${WP_ROOT}/wp-content/plugins/gl-color-palette-generator"

# Get Lando info if available
if command -v lando >/dev/null 2>&1; then
    echo "Getting Lando configuration..."
    LANDO_INFO=$(lando info --format=json)
    
    # Parse database details from Lando info using grep and sed
    if [ ! -z "$LANDO_INFO" ]; then
        # Extract database service info using grep and sed
        DB_HOST=$(echo "$LANDO_INFO" | grep -o '"host": "[^"]*"' | head -1 | sed 's/"host": "\(.*\)"/\1/')
        DB_USER=$(echo "$LANDO_INFO" | grep -o '"user": "[^"]*"' | head -1 | sed 's/"user": "\(.*\)"/\1/')
        DB_PASS=$(echo "$LANDO_INFO" | grep -o '"password": "[^"]*"' | head -1 | sed 's/"password": "\(.*\)"/\1/')
        TEST_DB_NAME=${TEST_DB_NAME:-"wordpress_test"}
        
        # If any values are null or empty, fall back to defaults
        DB_HOST=${DB_HOST:-"database"}
        DB_USER=${DB_USER:-"wordpress"}
        DB_PASS=${DB_PASS:-"wordpress"}
    fi
fi

# Fall back to environment variables or defaults for database config
DB_HOST=${DB_HOST:-"database"}
DB_USER=${DB_USER:-"wordpress"}
DB_PASS=${DB_PASS:-"wordpress"}
DB_NAME=${TEST_DB_NAME:-"wordpress_test"}
WP_VERSION=${WP_VERSION:-"latest"}

# Test environment paths
WP_TESTS_DIR=${WP_TESTS_DIR:-"${WP_ROOT}/wordpress-phpunit"}
WP_CORE_DIR=${WP_CORE_DIR:-"${WP_ROOT}/wordpress"}

echo "=== Environment Configuration ==="
echo "WordPress root: $WP_ROOT"
echo "Plugin source: $PLUGIN_SOURCE"
echo "Plugin directory: $PLUGIN_DIR"
echo "Database host: $DB_HOST"
echo "Database name: $DB_NAME"
echo "Database user: $DB_USER"
echo "WordPress version: $WP_VERSION"
echo "=========================="

echo "=== Starting Plugin Test Environment Setup ==="

# Step 1: Install composer dependencies
echo "1. Installing Composer dependencies..."
cd "$PLUGIN_SOURCE"
composer install
if [ $? -ne 0 ]; then
    echo "❌ Composer install failed"
    exit 1
fi
echo "✅ Composer dependencies installed"

# Step 2: Set up test environment
echo -e "\n2. Setting up test environment..."

# Create the tests directory if it doesn't exist
mkdir -p "$WP_TESTS_DIR"

# Download WordPress test suite using git instead of svn
if [ ! -d "$WP_TESTS_DIR/includes" ]; then
    echo "Downloading WordPress test suite..."
    git clone --depth=1 https://github.com/WordPress/wordpress-develop.git "$WP_TESTS_DIR/tmp"
    mv "$WP_TESTS_DIR/tmp/tests/phpunit/includes" "$WP_TESTS_DIR/"
    mv "$WP_TESTS_DIR/tmp/tests/phpunit/data" "$WP_TESTS_DIR/"
    mv "$WP_TESTS_DIR/tmp/tests/phpunit/tests" "$WP_TESTS_DIR/"
    rm -rf "$WP_TESTS_DIR/tmp"
fi

# Create test database if it doesn't exist
echo "Creating test database if it doesn't exist..."
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;" || {
    echo "Failed to create test database. Please check your database credentials."
    exit 1
}

# Generate wp-tests-config.php
echo "Generating wp-tests-config.php..."
cat > "${WP_TESTS_DIR}/wp-tests-config.php" << EOL
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

echo "✅ Test environment setup complete!"

# Step 3: Run the tests
echo -e "\n3. Running tests..."
cd "$PLUGIN_SOURCE"
vendor/bin/phpunit
