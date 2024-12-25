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
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS $DB_NAME;"
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"

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

# Get the original plugin source directory (before potentially changing directories)
SCRIPT_DIR="$( cd -- "$( dirname -- "${BASH_SOURCE[0]:-$0}" )" &> /dev/null && pwd )"
PLUGIN_SOURCE="$( cd -- "$SCRIPT_DIR/.." &> /dev/null && pwd )"

# If we're in the plugin directory inside WordPress, go up to WordPress root
if [ -f "composer.json" ] && [[ "$PWD" == */wp-content/plugins/* ]]; then
    cd "$(dirname "$(dirname "$(dirname "$PWD")")")"  # Go up three directories more safely
fi

# Check if wp-config.php exists in current directory
if [ ! -f "wp-config.php" ]; then
    echo "❌ Error: wp-config.php not found. Please run this script from the WordPress root directory."
    exit 1
fi

# Define paths (use environment variables if set, otherwise use defaults)
WP_ROOT=${WP_ROOT:-"$PWD"}
PLUGIN_DIR="${WP_ROOT}/wp-content/plugins/gl-color-palette-generator"

# Get PHP version and select appropriate composer file
PHP_VERSION=$(php -r "echo PHP_VERSION;")
PHP_MAJOR_MINOR=$(php -r "echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;")

echo "Detected PHP version: $PHP_VERSION"

# Compare PHP version
if [[ "$(printf '%s\n' "8.1" "$PHP_MAJOR_MINOR" | sort -V | head -n1)" == "8.1" && "$PHP_MAJOR_MINOR" == "8.1" ]]; then
    echo "Using PHP 8.1 compatible dependencies"
    COMPOSER_FILE="composer.php81.json"
elif [[ "$(printf '%s\n' "8.2" "$PHP_MAJOR_MINOR" | sort -V | head -n1)" == "8.2" ]]; then
    echo "Using PHP 8.2+ compatible dependencies"
    COMPOSER_FILE="composer.php82.json"
else
    echo "Unsupported PHP version: $PHP_VERSION (requires PHP 8.1 or higher)"
    exit 1
fi

# Get Lando info if available - this will override wp-config.php settings if running in Lando
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

        # Set paths for Lando environment
        WP_ROOT="/app"
        WP_CONFIG_PATH="$WP_ROOT/wp-config.php"
        PLUGIN_DIR="$WP_ROOT/wp-content/plugins/gl-color-palette-generator"
    else
        echo "Could not parse Lando database configuration, using local settings"
        echo "Debug info:"
        echo "  Host: '$DB_HOST'"
        echo "  User: '$DB_USER'"
        echo "  Pass length: ${#DB_PASS}"
    fi
else
    echo "Not in a Lando environment, using local settings"
fi

# Set test database name
DB_NAME="wordpress_test"

# Fall back to environment variables or defaults for database config
DB_HOST=${DB_HOST:-"localhost"}
DB_USER=${DB_USER:-"root"}
DB_PASS=${DB_PASS:-""}

# Test environment paths
WP_TESTS_DIR=${WP_TESTS_DIR:-"${WP_ROOT}/wordpress-phpunit"}

echo "=== Environment Configuration ==="
echo "WordPress root: $WP_ROOT"
echo "Plugin directory: $PLUGIN_DIR"
echo "Database host: $DB_HOST"
echo "Database name: $DB_NAME"
echo "Database user: $DB_USER"
echo "WordPress version: ${WP_VERSION:-"latest"}"
echo "=========================="

echo "=== Starting Plugin Test Environment Setup ==="

# Step 1: Install composer dependencies
echo "1. Installing Composer dependencies..."

# Compare PHP version
if [[ "$(printf '%s\n' "8.1" "$PHP_MAJOR_MINOR" | sort -V | head -n1)" == "8.1" && "$PHP_MAJOR_MINOR" == "8.1" ]]; then
    echo "Using PHP 8.1 compatible dependencies"
    COMPOSER_FILE="composer.php81.json"
elif [[ "$(printf '%s\n' "8.2" "$PHP_MAJOR_MINOR" | sort -V | head -n1)" == "8.2" ]]; then
    echo "Using PHP 8.2+ compatible dependencies"
    COMPOSER_FILE="composer.php82.json"
else
    echo "Unsupported PHP version: $PHP_VERSION (requires PHP 8.1 or higher)"
    exit 1
fi

# Copy appropriate composer file and remove lock file
echo "Setting up composer dependencies for PHP $PHP_VERSION..."
if [ ! -f "$PLUGIN_SOURCE/$COMPOSER_FILE" ]; then
    echo "❌ Error: $COMPOSER_FILE not found in plugin source directory: $PLUGIN_SOURCE"
    exit 1
fi

cp "$PLUGIN_SOURCE/$COMPOSER_FILE" composer.json
rm -f composer.lock

# Install dependencies
echo "Installing fresh dependencies..."
composer install --no-interaction
if [ $? -ne 0 ]; then
    echo "❌ Composer install failed"
    exit 1
fi

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

# In Lando environment, use lando mysql
if [ -d "$WP_ROOT" ] && cd "$WP_ROOT" && command -v lando >/dev/null 2>&1; then
    echo "Using Lando MySQL..."

    # Check if database exists
    if lando mysql -e "SHOW DATABASES LIKE '$DB_NAME'" | grep -q "$DB_NAME"; then
        echo "Test database '$DB_NAME' already exists"
    else
        echo "Creating test database '$DB_NAME'..."
        lando mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME"
    fi
    cd - >/dev/null
else
    # Not in Lando environment, use regular mysql
    if [ -n "$DB_PASS" ]; then
        mysql_cmd="mysql -h$DB_HOST -u$DB_USER -p$DB_PASS"
    else
        mysql_cmd="mysql -h$DB_HOST -u$DB_USER"
    fi

    # Check if database exists
    if echo "SHOW DATABASES LIKE '$DB_NAME'" | $mysql_cmd | grep -q "$DB_NAME"; then
        echo "Test database '$DB_NAME' already exists"
    else
        echo "Creating test database '$DB_NAME'..."
        echo "CREATE DATABASE IF NOT EXISTS $DB_NAME" | $mysql_cmd
    fi
fi

if [ $? -ne 0 ]; then
    echo "❌ Failed to create test database"
    exit 1
fi

# Generate wp-tests-config.php file
echo "Generating wp-tests-config.php..."
if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
    if [ ! -f "$WP_TESTS_DIR/wp-tests-config-sample.php" ]; then
        # Download wp-tests-config-sample.php if it doesn't exist
        echo "Downloading wp-tests-config-sample.php..."
        curl -s https://raw.githubusercontent.com/WordPress/wordpress-develop/trunk/wp-tests-config-sample.php > "$WP_TESTS_DIR/wp-tests-config-sample.php"
    fi

    sed \
        -e "s/youremptytestdbnamehere/$DB_NAME/" \
        -e "s/yourusernamehere/$DB_USER/" \
        -e "s/yourpasswordhere/$DB_PASS/" \
        -e "s|localhost|${DB_HOST}|" \
        "$WP_TESTS_DIR/wp-tests-config-sample.php" > "$WP_TESTS_DIR/wp-tests-config.php"
fi

# Install WordPress test framework
echo "Installing WordPress test framework..."

# Download WordPress test suite using git instead of svn
if [ ! -d "$WP_TESTS_DIR/includes" ]; then
    echo "Downloading WordPress test suite..."
    git clone --depth=1 https://github.com/WordPress/wordpress-develop.git "$WP_TESTS_DIR/tmp"
    mv "$WP_TESTS_DIR/tmp/tests/phpunit/includes" "$WP_TESTS_DIR/"
    mv "$WP_TESTS_DIR/tmp/tests/phpunit/data" "$WP_TESTS_DIR/"
    mv "$WP_TESTS_DIR/tmp/tests/phpunit/tests" "$WP_TESTS_DIR/"
    rm -rf "$WP_TESTS_DIR/tmp"
fi

# Create temporary PHP file to run installation
TMP_PHP_FILE="$WP_TESTS_DIR/install-wp-tests.php"
cat > "$TMP_PHP_FILE" << 'EOF'
<?php
// Define WordPress test environment
define('ABSPATH', '/app/');
define('WP_TESTS_CONFIG_FILE_PATH', dirname(__FILE__) . '/wp-tests-config.php');

// Include WordPress test framework files
require_once dirname(__FILE__) . '/includes/functions.php';
require_once dirname(__FILE__) . '/includes/install.php';

// Run the installation
tests_install(dirname(__FILE__) . '/data');
EOF

echo "Running PHP script..."
php "$TMP_PHP_FILE" "$WP_TESTS_DIR/wp-tests-config.php"
rm "$TMP_PHP_FILE"

# Verify database tables after WordPress test setup
verify_database_tables() {
    echo "Verifying database tables..."

    # Use lando mysql in Lando environment
    if [ -d "$WP_ROOT" ] && cd "$WP_ROOT" && command -v lando >/dev/null 2>&1; then
        local tables=$(lando mysql -e "SHOW TABLES FROM $DB_NAME" | grep -v "Tables_in_$DB_NAME")
        cd - >/dev/null
    else
        local tables=$($mysql_cmd -N -e "SHOW TABLES FROM $DB_NAME")
    fi

    if [ -z "$tables" ]; then
        echo "❌ No tables found in database $DB_NAME"
        return 1
    fi

    # Check for essential WordPress tables
    local required_tables=("comments" "options" "posts" "terms" "users")
    local missing_tables=()

    for table in "${required_tables[@]}"; do
        if ! echo "$tables" | grep -q "wp_$table"; then
            missing_tables+=("wp_$table")
        fi
    done

    # Check for plugin-specific tables and their structure
    local plugin_table="wp_gl_color_palettes"
    if ! echo "$tables" | grep -q "$plugin_table"; then
        missing_tables+=("$plugin_table")
    else
        # Verify plugin table structure
        if [ -d "$WP_ROOT" ] && cd "$WP_ROOT" && command -v lando >/dev/null 2>&1; then
            local table_info=$(lando mysql -e "DESCRIBE $DB_NAME.$plugin_table" | awk '{print $1}')
            cd - >/dev/null
        else
            local table_info=$($mysql_cmd -N -e "DESCRIBE $DB_NAME.$plugin_table")
        fi

        local required_columns=("id" "name" "colors" "created_at" "updated_at")
        local missing_columns=()

        for column in "${required_columns[@]}"; do
            if ! echo "$table_info" | grep -q "^$column"; then
                missing_columns+=("$column")
            fi
        done

        if [ ${#missing_columns[@]} -gt 0 ]; then
            echo "❌ Missing columns in $plugin_table: ${missing_columns[*]}"
            return 1
        fi
    fi

    if [ ${#missing_tables[@]} -eq 0 ]; then
        echo "✅ All required database tables are present with correct structure"
        return 0
    else
        echo "❌ Missing required tables: ${missing_tables[*]}"
        return 1
    fi
}

# Run database verification after WordPress test setup
verify_database_tables
if [ $? -eq 0 ]; then
    echo "✅ WordPress plugin test setup completed successfully."
    if [ ! -z "${LANDO_INFO:-}" ]; then
        echo ""
        echo "🔄 IMPORTANT: Run this command to apply all changes:"
        echo "   cd ${WP_ROOT} && lando rebuild -y"
        echo ""
    fi
    exit 0
else
    echo "❌ Database verification failed"
    exit 1
fi

# Step 3: Run the tests
echo -e "\n3. Running tests..."
cd "$PLUGIN_DIR"
vendor/bin/phpunit
