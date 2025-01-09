#!/usr/bin/env bash

# Exit if any command fails
set -e

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

# Change to plugin root directory
cd "$(dirname "$0")/.."
PLUGIN_DIR=$(pwd)
PLUGIN_SLUG=$(basename "$PLUGIN_DIR")

# Set default paths and database settings from WordPress config
if [ ! -z "${LANDO_INFO:-}" ]; then
    # In Lando environment, use LANDO_WEBROOT but clean up the path
    WP_ROOT=$(echo "${LANDO_WEBROOT:-/app}" | sed 's/\/\.//')
else
    # For local environment, try to find WordPress root
    local_root=$(find_wordpress_root "$(pwd)")
    if [ $? -eq 0 ]; then
        WP_ROOT="$local_root"
    else
        echo "Error: Could not find WordPress root directory (wp-config.php not found)"
        exit 1
    fi
fi

WP_CONFIG_PATH=$(cd "$WP_ROOT" && pwd)/wp-config.php
if [ -f "$WP_CONFIG_PATH" ]; then
    echo "Reading WordPress configuration from $WP_CONFIG_PATH"
    DB_NAME=$(get_wp_config_value "DB_NAME" "$WP_CONFIG_PATH")
    DB_USER=$(get_wp_config_value "DB_USER" "$WP_CONFIG_PATH")
    DB_PASS=$(get_wp_config_value "DB_PASSWORD" "$WP_CONFIG_PATH")
    DB_HOST=$(get_wp_config_value "DB_HOST" "$WP_CONFIG_PATH")
    WP_ROOT=$(cd "$WP_ROOT" && pwd)
else
    echo "Warning: wp-config.php not found at $WP_CONFIG_PATH"
    # Fallback values
    DB_NAME='wordpress_test'
    DB_USER='root'
    DB_PASS=''
    DB_HOST='localhost'
    WP_ROOT=$(cd "$WP_ROOT" && pwd)
fi

# Get Lando info if available
if [ ! -z "${LANDO_INFO:-}" ]; then
    echo "Getting Lando configuration..."
    # Extract database service info
    DB_HOST=$(echo "$LANDO_INFO" | grep -o '"internal_connection":{[^}]*}' | grep -o '"host":"[^"]*"' | head -1 | cut -d'"' -f4)
    DB_USER=$(echo "$LANDO_INFO" | grep -o '"creds":{[^}]*}' | grep -o '"user":"[^"]*"' | head -1 | cut -d'"' -f4)
    DB_PASS=$(echo "$LANDO_INFO" | grep -o '"creds":{[^}]*}' | grep -o '"password":"[^"]*"' | head -1 | cut -d'"' -f4)
    DB_NAME="wordpress_test"  # Set the test database name explicitly

    if [ ! -z "$DB_HOST" ] && [ ! -z "$DB_USER" ]; then
        echo "Using Lando database configuration:"
        echo "  Host: $DB_HOST"
        echo "  User: $DB_USER"
        echo "  Test Database will be: $DB_NAME"

        # Override paths for Lando environment
        WP_ROOT="/app"
        WP_CONFIG_PATH="$WP_ROOT/wp-config.php"
        WP_TESTS_DIR="$WP_ROOT/wp-content/plugins/wordpress-develop/tests/phpunit"
        PLUGIN_DIR="$WP_ROOT/wp-content/plugins/gl-color-palette-generator"
    fi
fi

# Set up WordPress test suite directory
if [ -z "$WP_TESTS_DIR" ]; then
    WP_TESTS_DIR="$WP_ROOT/wordpress-phpunit"
fi

# Create tests directory if it doesn't exist
mkdir -p "$WP_TESTS_DIR"

# Download WordPress test suite
download_wp_tests() {
    # Download WordPress test suite using git
    if [ ! -d "$WP_TESTS_DIR/includes" ]; then
        echo "Downloading WordPress test suite..."
        git clone --depth=1 https://github.com/WordPress/wordpress-develop.git "$WP_TESTS_DIR/tmp"

        # Move required directories, preserving directory structure
        cp -r "$WP_TESTS_DIR/tmp/tests/phpunit/includes" "$WP_TESTS_DIR/"
        cp -r "$WP_TESTS_DIR/tmp/tests/phpunit/data" "$WP_TESTS_DIR/"
        cp -r "$WP_TESTS_DIR/tmp/tests/phpunit/tests" "$WP_TESTS_DIR/"

        # Cleanup
        rm -rf "$WP_TESTS_DIR/tmp"

        # Verify files exist
        if [ ! -f "$WP_TESTS_DIR/includes/functions.php" ] || [ ! -f "$WP_TESTS_DIR/includes/install.php" ]; then
            echo "Error: Failed to download WordPress test suite files to folder $WP_TESTS_DIR/includes/functions.php or $WP_TESTS_DIR/includes/install.php"
            exit 1
        fi
    fi
}

# Generate wp-tests-config.php
generate_wp_tests_config() {
    echo "Generating wp-tests-config.php..."
    cat > "$WP_TESTS_DIR/wp-tests-config.php" << EOF
<?php
/* Path to the WordPress codebase you'd like to test. Add a forward slash in the end. */
if (!defined('ABSPATH')) {
    define( 'ABSPATH', '$WP_ROOT/' );
}

/* Test with WordPress debug mode on */
define( 'WP_DEBUG', true );

/* Database settings */
define( 'DB_NAME', '$DB_NAME' );
define( 'DB_USER', '$DB_USER' );
define( 'DB_PASSWORD', '$DB_PASS' );
define( 'DB_HOST', '$DB_HOST' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

define( 'WP_PHP_BINARY', 'php' );

\$table_prefix = 'wptests_';
EOF

    # Create symlink to wp-tests-config.php in the tests directory
    echo "Creating symlink to wp-tests-config.php..."
    ln -sf "$WP_TESTS_DIR/wp-tests-config.php" "$PLUGIN_DIR/tests/wp-tests-config.php"
}

# Install test database
install_test_suite() {
    # Create database if it doesn't exist
    echo "Setting up test database..."
    echo "Debug: Database parameters:"
    echo "  Host: $DB_HOST"
    echo "  User: $DB_USER"
    echo "  Name: $DB_NAME"
    echo "  Password: $DB_PASS"
    echo "  Password length: ${#DB_PASS}"
    echo "SQL commands to be executed:"
    echo "  DROP DATABASE IF EXISTS $DB_NAME;"
    echo "  CREATE DATABASE IF NOT EXISTS $DB_NAME;"

    # Check if MySQL is reachable
    # Mysql expects there should be no space between -p and the password value.
    # should be this command:
    # mysql -h "database" -u "wordpress" -p"wordpress" -e "SELECT 1"
    if ! mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1" >/dev/null 2>&1; then
        echo "Error: Cannot connect to MySQL server"
        exit 1
    fi

    # Try to drop database if exists
    if ! mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS $DB_NAME"; then
        echo "Error: Failed to drop test database. Check user permissions."
        exit 1
    fi

    # Try to create database
    if ! mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME"; then
        echo "Error: Failed to create test database. Check user permissions."
        exit 1
    fi

    # Verify database exists
    if ! mysql -h "$DB_HOST" -u "$DB_USER" $DB_PASS_ARG -e "USE $DB_NAME"; then
        echo "Error: Cannot access test database after creation"
        exit 1
    fi

    echo "✅ Test database created and verified"

    # Install WordPress test framework
    echo "Installing WordPress test framework..."

    # Verify required files exist
    if [ ! -f "$WP_TESTS_DIR/includes/functions.php" ] || [ ! -f "$WP_TESTS_DIR/includes/install.php" ]; then
        echo "Error: WordPress test framework files not found. Please check the installation."
        exit 1
    fi

    cd "$WP_TESTS_DIR"

    # Create a temporary PHP script to run the installation
    cat > install-wp-tests.php << EOF
<?php
\$_SERVER['argv'] = array(
    'install-wp-tests.php',
    '$WP_TESTS_DIR/wp-tests-config.php'
);
require_once '$WP_TESTS_DIR/includes/functions.php';
require_once '$WP_TESTS_DIR/includes/install.php';

echo "Installing...\n";
tests_install('$WP_TESTS_DIR/data');
EOF

    # Execute the PHP script
    php "$WP_TESTS_DIR/includes/install.php" "$WP_TESTS_DIR/wp-tests-config.php"
    rm install-wp-tests.php
}

# Main execution
echo "Setting up WordPress plugin tests..."

# Check system requirements
check_system_requirements

# Download and set up test suite
download_wp_tests

# Generate config file
generate_wp_tests_config

# Install test suite
install_test_suite

echo "WordPress plugin test setup completed successfully."
