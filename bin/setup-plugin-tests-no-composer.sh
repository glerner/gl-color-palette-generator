#!/bin/bash

set -e  # Exit on any error

# Utility functions
check_system_requirements() {
    # Check if git is available
    if ! command -v git >/dev/null 2>&1; then
        echo "Error: git is required but not installed."
        exit 1
    fi

    # Check if mysql client is available
    if ! command -v mysql >/dev/null 2>&1; then
        echo "Error: mysql client is required but not installed."
        exit 1
    fi

    # Check if PHP is available
    if ! command -v php >/dev/null 2>&1; then
        echo "Error: PHP is required but not installed."
        exit 1
    fi
}

# Function to find WordPress root by looking for wp-config.php
find_wordpress_root() {
    local current_dir="$1"
    local max_depth=5  # Don't go up more than 5 directories
    local depth=0

    while [ $depth -lt $max_depth ]; do
        if [ -f "$current_dir/wp-config.php" ]; then
            echo "$(cd "$current_dir" && pwd)"  # Normalize path
            return 0
        fi
        current_dir="$(dirname "$current_dir")"
        depth=$((depth + 1))
    done
    return 1
}

# Function to get WordPress config values
get_wp_config_value() {
    local search_value="$1"
    local wp_config_path="$2"
    local value=$(grep -o "define.*['\"]\?${search_value}['\"]\?.*" "$wp_config_path" | cut -d',' -f2 | sed "s/[',\")]//g" | sed 's/^[ \t]*//')
    echo "$value"
}

# Cleanup function for failed installations
cleanup_on_error() {
    echo "Cleaning up after error..."
    [ -f "$WP_TESTS_DIR/wp-tests-config.php" ] && rm "$WP_TESTS_DIR/wp-tests-config.php"
    [ -f "$PLUGIN_DIR/wp-tests-config.php" ] && rm "$PLUGIN_DIR/wp-tests-config.php"
    [ -f "$WP_ROOT/wp-tests-config.php" ] && rm "$WP_ROOT/wp-tests-config.php"
    [ -d "$WP_TESTS_DIR" ] && rm -rf "$WP_TESTS_DIR"
    exit 1
}
trap cleanup_on_error ERR

# Check system requirements
check_system_requirements

# Determine if we're running in Lando
IS_LANDO=false
if [ -n "${LANDO_INFO}" ]; then
    IS_LANDO=true
    echo "Detected Lando environment"
fi

# Define paths
PLUGIN_SOURCE=${PLUGIN_SOURCE:-"$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"}

# Find WordPress root
if [ -z "${WP_ROOT}" ]; then
    WP_ROOT=$(find_wordpress_root "$PLUGIN_SOURCE")
    if [ $? -ne 0 ]; then
        echo "Error: Could not find WordPress root directory"
        exit 1
    fi
fi

if [ ! -d "$WP_ROOT" ]; then
    echo "Error: WordPress root directory does not exist: $WP_ROOT"
    exit 1
fi

# Set plugin directory
PLUGIN_DIR="${WP_ROOT}/wp-content/plugins/gl-color-palette-generator"

# Test environment paths
WP_TESTS_DIR=${WP_TESTS_DIR:-"${WP_ROOT}/wordpress-phpunit"}
WP_CORE_DIR=${WP_CORE_DIR:-"${WP_ROOT}/wordpress"}

# Read configuration from wp-config.php if available
if [ -f "$WP_ROOT/wp-config.php" ]; then
    DB_NAME=$(get_wp_config_value "DB_NAME" "$WP_ROOT/wp-config.php")
    DB_USER=$(get_wp_config_value "DB_USER" "$WP_ROOT/wp-config.php")
    DB_PASSWORD=$(get_wp_config_value "DB_PASSWORD" "$WP_ROOT/wp-config.php")
    DB_HOST=$(get_wp_config_value "DB_HOST" "$WP_ROOT/wp-config.php")
fi

# Set defaults if values weren't found in wp-config.php
DB_NAME=${TEST_DB_NAME:-${DB_NAME:-"wordpress_test"}}
DB_USER=${TEST_DB_USER:-${DB_USER:-"wordpress"}}
DB_PASSWORD=${TEST_DB_PASS:-${DB_PASSWORD:-"wordpress"}}
if $IS_LANDO; then
    DB_HOST=${TEST_DB_HOST:-${DB_HOST:-"database"}}
else
    DB_HOST=${TEST_DB_HOST:-${DB_HOST:-"localhost"}}
fi

WP_VERSION=${WP_VERSION:-"latest"}
SKIP_DB_CREATE=${SKIP_DB_CREATE:-"false"}

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

if [ ! -f "$PLUGIN_SOURCE/bin/sync-to-wp.sh" ]; then
    echo "Error: sync-to-wp.sh script not found"
    exit 1
fi

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

# Ensure plugin directory exists, before make wp-tests-config.php
if [ ! -d "$PLUGIN_DIR" ]; then
    echo "Error: Plugin directory does not exist: $PLUGIN_DIR"
    exit 1
fi

if [ ! -w "$PLUGIN_DIR" ] || [ ! -w "$WP_ROOT" ]; then
    echo "Error: Insufficient write permissions in plugin or WordPress directories"
    exit 1
fi

# Create wp-tests-config.php with environment-specific settings

if ! cat > "${PLUGIN_DIR}/wp-tests-config.php" << EOL
<?php
// Test with WordPress debug mode (default)
define( 'WP_DEBUG', true );

// ** MySQL settings ** //
define( 'DB_NAME', '${DB_NAME}' );
define( 'DB_USER', '${DB_USER}' );
define( 'DB_PASSWORD', '${DB_PASSWORD}' );
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
then
    echo "Error: Failed to create config file in plugin directory"
    exit 1
fi

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
define( 'DB_PASSWORD', '${DB_PASSWORD}' );
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
then
    echo "Error: Failed to create config file in WordPress root"
    exit 1
fi

echo "✅ Test configuration files created"

# Download WordPress test suite
if [ ! -d "$WP_TESTS_DIR" ]; then
    echo "Downloading WordPress test suite..."

    # Create tests directory if it doesn't exist
    mkdir -p "$WP_TESTS_DIR"

    # Clone or update the test suite
    if [ -d "$WP_TESTS_DIR/.git" ]; then
        echo "WordPress develop repository already exists, updating..."
        cd "$WP_TESTS_DIR"
        git pull
    else
        git clone --depth=1 https://github.com/WordPress/wordpress-develop.git "$WP_TESTS_DIR"
    fi

    # Copy the test config file

    if [ ! -f "$WP_TESTS_DIR/wp-tests-config-sample.php" ]; then
        echo "Error: WordPress test suite appears to be incomplete"
        exit 1
    fi

    cp "$WP_TESTS_DIR/wp-tests-config-sample.php" "$WP_TESTS_DIR/wp-tests-config.php"

    # Update test config file with improved error handling
    if ! sed -i "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR/wp-tests-config.php"; then
        echo "Error: Failed to configure database name in test config"
        exit 1
    fi
    if ! sed -i "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR/wp-tests-config.php"; then
        echo "Error: Failed to configure database user in test config"
        exit 1
    fi
    if ! sed -i "s/yourpasswordhere/$DB_PASSWORD/" "$WP_TESTS_DIR/wp-tests-config.php"; then
        echo "Error: Failed to configure database password in test config"
        exit 1
    fi
    if ! sed -i "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR/wp-tests-config.php"; then
        echo "Error: Failed to configure database host in test config"
        exit 1
    fi

    echo "WordPress test suite downloaded and configured successfully"
fi

# Step 4: Setup test database
echo -e "\n4. Setting up test database..."

# Create test database if it doesn't exist
if [ "${SKIP_DB_CREATE}" != "true" ]; then
    # Handle potential password argument for mysql
    DB_PASS_ARG=""
    if [ ! -z "$DB_PASSWORD" ]; then
        DB_PASS_ARG="-p$DB_PASSWORD"
    fi

    # Check if MySQL is reachable
    if ! mysql -h "$DB_HOST" -u "$DB_USER" $DB_PASS_ARG -e "SELECT 1" >/dev/null 2>&1; then
        echo "Error: Cannot connect to MySQL server"
        exit 1
    fi

    # Try to create database
    if ! mysql -h "$DB_HOST" -u "$DB_USER" $DB_PASS_ARG -e "CREATE DATABASE IF NOT EXISTS $DB_NAME"; then
        echo "Error: Failed to create test database"
        exit 1
    fi

    # Verify database exists
    if ! mysql -h "$DB_HOST" -u "$DB_USER" $DB_PASS_ARG -e "USE $DB_NAME"; then
        echo "Error: Cannot access test database after creation"
        exit 1
    fi

    echo "✅ Test database created and verified"
else
    echo "Skipping database creation (SKIP_DB_CREATE=true)"
fi

# Final verification
echo -e "\nVerifying setup..."
if [ -d "$WP_TESTS_DIR" ] && [ -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
    echo "✅ WordPress test suite is properly configured"
else
    echo "❌ WordPress test suite setup appears incomplete"
    exit 1
fi

echo -e "\n✅ Plugin test environment setup complete!"
