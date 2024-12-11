#!/bin/bash

set -e  # Exit on any error

# Define paths (use environment variables if set, otherwise use defaults)
WP_ROOT=${WP_ROOT:-"/home/george/sites/wordpress"}
PLUGIN_SOURCE=${PLUGIN_SOURCE:-"$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"}
PLUGIN_DIR="${WP_ROOT}/wp-content/plugins/gl-color-palette-generator"

echo "Using paths:"
echo "WordPress root: $WP_ROOT"
echo "Plugin source: $PLUGIN_SOURCE"
echo "Plugin directory: $PLUGIN_DIR"

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

# Step 2: Rebuild Lando environment
echo -e "\n2. Rebuilding Lando environment..."
cd "$WP_ROOT"
lando rebuild -y
if [ $? -ne 0 ]; then
    echo "❌ Lando rebuild failed"
    exit 1
fi

# Wait for services to be ready
echo "Waiting for services to be ready..."
sleep 10

# Step 3: Sync plugin to WordPress
echo -e "\n3. Syncing plugin to WordPress..."
cd "$PLUGIN_SOURCE"
bash bin/sync-to-wp.sh
if [ $? -ne 0 ]; then
    echo "❌ Plugin sync failed"
    exit 1
fi
echo "✅ Plugin synced to WordPress"

# Step 4: Generate wp-tests-config.php
echo -e "\n4. Generating wp-tests-config.php..."
cd "$WP_ROOT"

# Get Lando info
echo "Getting Lando info..."
LANDO_INFO=$(lando info)
echo "$LANDO_INFO" | grep -A5 'internal_connection:'

DOMAIN=$(echo "$LANDO_INFO" | grep -o 'https://.*\.lndo\.site' | head -n1 | sed 's/https:\/\///')
DB_HOST='database'  # This is always 'database' in Lando
DB_USER='wordpress'  # Default WordPress Lando credentials
DB_PASS='wordpress'
DB_NAME='wordpress'

echo "Using configuration:"
echo "Domain: $DOMAIN"
echo "DB Host: $DB_HOST"
echo "DB User: $DB_USER"
echo "DB Name: $DB_NAME"

# Create wp-tests-config.php content
CONFIG_CONTENT="<?php
// Required constants for WordPress test suite
define( 'WP_TESTS_DOMAIN', '$DOMAIN' );
define( 'WP_TESTS_EMAIL', 'admin@example.com' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );

// Database configuration
define( 'DB_NAME', '$DB_NAME' );
define( 'DB_USER', '$DB_USER' );
define( 'DB_PASSWORD', '$DB_PASS' );
define( 'DB_HOST', '$DB_HOST' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

\$table_prefix = 'wptests_';

// Test suite database configuration
define( 'WP_TESTS_DB_NAME', '$DB_NAME' );
define( 'WP_TESTS_DB_USER', '$DB_USER' );
define( 'WP_TESTS_DB_PASSWORD', '$DB_PASS' );
define( 'WP_TESTS_DB_HOST', '$DB_HOST' );

// WordPress core constants
define( 'ABSPATH', dirname(__FILE__) . '/' );
define( 'WP_DEBUG', true );"

echo "Creating config files in:"
echo "1. $WP_ROOT/wp-tests-config.php"
echo "2. $PLUGIN_DIR/wp-tests-config.php"

# Create wp-tests-config.php in WordPress root
echo "$CONFIG_CONTENT" > "$WP_ROOT/wp-tests-config.php"
if [ ! -f "$WP_ROOT/wp-tests-config.php" ]; then
    echo "❌ Failed to create wp-tests-config.php in WordPress root"
    exit 1
fi

# Create wp-tests-config.php in plugin directory
echo "$CONFIG_CONTENT" > "$PLUGIN_DIR/wp-tests-config.php"
if [ ! -f "$PLUGIN_DIR/wp-tests-config.php" ]; then
    echo "❌ Failed to create wp-tests-config.php in plugin directory"
    exit 1
fi

# Also create in tests directory
mkdir -p "$PLUGIN_DIR/tests"
echo "$CONFIG_CONTENT" > "$PLUGIN_DIR/tests/wp-tests-config.php"
if [ ! -f "$PLUGIN_DIR/tests/wp-tests-config.php" ]; then
    echo "❌ Failed to create wp-tests-config.php in tests directory"
    exit 1
fi

# Verify files exist in Lando container
echo "Verifying files in Lando container..."
lando ssh -c "ls -l /app/wp-tests-config.php /app/wp-content/plugins/gl-color-palette-generator/wp-tests-config.php /app/wp-content/plugins/gl-color-palette-generator/tests/wp-tests-config.php"
if [ $? -ne 0 ]; then
    echo "❌ Files not found in Lando container"
    exit 1
fi

echo "✅ wp-tests-config.php created and verified in all locations"

# Step 5: Install WordPress test suite
echo -e "\n5. Installing WordPress test suite..."
lando setup-wp-tests
if [ $? -ne 0 ]; then
    echo "❌ WordPress test suite installation failed"
    exit 1
fi
echo "✅ WordPress test suite installed"

# Step 6: Verify environment
echo -e "\n=== Verifying Environment ==="

# Check if WordPress is responding
echo "6a. Checking WordPress site..."
max_retries=5
retry_count=0
while [ $retry_count -lt $max_retries ]; do
    if curl -sI "https://$DOMAIN/" | grep "HTTP/"; then
        echo "✅ WordPress site is responding"
        break
    fi
    echo "Waiting for WordPress to respond... (attempt $((retry_count + 1))/$max_retries)"
    sleep 5
    retry_count=$((retry_count + 1))
done

if [ $retry_count -eq $max_retries ]; then
    echo "❌ WordPress site check failed after $max_retries attempts"
    exit 1
fi

# Check if database is accessible
echo -e "\n6b. Checking database connection..."
if ! lando mysql -e "SELECT 1;" >/dev/null 2>&1; then
    echo "❌ Database connection failed"
    exit 1
fi
echo "✅ Database is accessible"

# Check if PHPUnit is installed and working
echo -e "\n6c. Checking PHPUnit installation..."
cd "$PLUGIN_SOURCE"
if ! lando composer exec phpunit -- --version; then
    echo "❌ PHPUnit check failed"
    exit 1
fi
echo "✅ PHPUnit is installed correctly"

echo -e "\n=== Setup Complete ==="
echo "You can now run tests with: lando test"
