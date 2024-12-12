#!/bin/bash

set -e  # Exit on any error

# Define paths (use environment variables if set, otherwise use defaults)
WP_ROOT=${WP_ROOT:-"/home/george/sites/wordpress"}
PLUGIN_SOURCE=${PLUGIN_SOURCE:-"$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"}
PLUGIN_DIR="${WP_ROOT}/wp-content/plugins/gl-color-palette-generator"

# Database configuration
# Customize these for your environment by setting environment variables, e.g.:
# export DB_NAME="your_test_db"
# export DB_USER="your_db_user"
# export DB_PASS="your_db_password"
# export DB_HOST="localhost"  # Use 'database' for Lando, 'localhost' for Local
DB_NAME=${DB_NAME:-"wordpress"}
DB_USER=${DB_USER:-"wordpress"}
DB_PASS=${DB_PASS:-"wordpress"}
DB_HOST=${DB_HOST:-"database"}  # Lando uses 'database', Local typically uses 'localhost'
WP_VERSION=${WP_VERSION:-"latest"}
SKIP_DB_CREATE=${SKIP_DB_CREATE:-"false"}

# Test environment paths (customize these for your environment)
# For Lando, these are set in .lando.yml
WP_TESTS_DIR=${WP_TESTS_DIR:-"${WP_ROOT}/wordpress-phpunit"}
WP_CORE_DIR=${WP_CORE_DIR:-"${WP_ROOT}/wordpress"}

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

# Create wp-tests-config.php in plugin directory
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

# Also create in tests directory
mkdir -p "$PLUGIN_DIR/tests"
cat > "${PLUGIN_DIR}/tests/wp-tests-config.php" << EOL
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

if [ ! -f "$PLUGIN_DIR/wp-tests-config.php" ]; then
    echo "Error: Failed to generate wp-tests-config.php. Check the error messages above for details."
    echo "This file is required for WordPress integration tests but not for WP-Mock tests."
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

# Set domain based on environment
if [ "$IS_LANDO" = true ]; then
    SITE_URL="https://lc.lndo.site/"
else
    SITE_URL="http://localhost/"  # Can be overridden with SITE_URL env var
fi
SITE_URL=${SITE_URL:-"http://localhost/"}

while [ $retry_count -lt $max_retries ]; do
    if curl -sI "$SITE_URL" | grep -q "200\|301\|302"; then
        echo "✅ WordPress site is responding"
        break
    fi
    echo "Waiting for WordPress to respond... (attempt $((retry_count + 1))/$max_retries)"
    sleep 5
    retry_count=$((retry_count + 1))
done

if [ $retry_count -eq $max_retries ]; then
    echo "⚠️  WordPress site check failed after $max_retries attempts"
    echo "This may be OK if you haven't set up WordPress yet."
    echo "Continuing with setup..."
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
