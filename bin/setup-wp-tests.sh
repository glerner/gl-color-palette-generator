#!/bin/bash

set -e

echo "Setting up WordPress tests environment..."

# Install WordPress test suite
cd /app
rm -rf wordpress-phpunit
svn co --quiet https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/ wordpress-phpunit/includes
svn co --quiet https://develop.svn.wordpress.org/trunk/tests/phpunit/data/ wordpress-phpunit/data
svn co --quiet https://develop.svn.wordpress.org/trunk/tests/phpunit/tests/ wordpress-phpunit/tests

# Create wp-tests-config.php
cat > wordpress-phpunit/wp-tests-config.php << EOF
<?php
define( 'ABSPATH', '/app/wordpress/' );
define( 'DB_NAME', 'wordpress' );
define( 'DB_USER', 'wordpress' );
define( 'DB_PASSWORD', 'wordpress' );
define( 'DB_HOST', 'database' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );

define( 'WPLANG', '' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );

\$table_prefix = 'wptests_';
EOF

echo "WordPress tests environment setup complete!"
