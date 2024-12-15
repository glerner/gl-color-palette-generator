#!/usr/bin/env bash

if [ $# -lt 3 ]; then
    echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]"
    exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-false}

TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo $TMPDIR | sed -e "s/\/$//")
WP_TESTS_DIR=${WP_TESTS_DIR-/app/wordpress-phpunit}
WP_CORE_DIR=${WP_CORE_DIR-/app/wordpress/}

# Debug output
echo "=== Debug Information ==="
echo "WP_VERSION = ${WP_VERSION}"
echo "WP_TESTS_DIR = ${WP_TESTS_DIR}"
echo "DB_HOST = ${DB_HOST}"
echo "=== End Debug Information ==="

set -ex

download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+\-(beta|RC)[0-9]+$ ]]; then
    WP_BRANCH=${WP_VERSION%\-*}
    WP_TESTS_TAG="branches/$WP_BRANCH"
elif [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+$ ]]; then
    WP_TESTS_TAG="branches/$WP_VERSION"
elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0-9]+ ]]; then
    if [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0] ]]; then
        WP_TESTS_TAG="tags/${WP_VERSION%??}"
    else
        WP_TESTS_TAG="tags/$WP_VERSION"
    fi
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
    WP_TESTS_TAG="trunk"
else
    # http serves a single offer, whereas https serves multiple. we only want one
    download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
    LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
    if [[ -z "$LATEST_VERSION" ]]; then
        echo "Latest WordPress version could not be found"
        exit 1
    fi
    WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

echo "=== Debug Information ==="
echo "WP_VERSION = ${WP_VERSION}"
echo "WP_TESTS_TAG = ${WP_TESTS_TAG}"
echo "WP_TESTS_DIR = ${WP_TESTS_DIR}"
echo "DB_HOST = ${DB_HOST}"
echo "=== End Debug Information ==="

if [ -d $WP_TESTS_DIR ]; then
    echo "Removing existing test directory..."
    rm -rf $WP_TESTS_DIR
fi

install_test_suite() {
    # portable in-place argument for both GNU sed and Mac OSX sed
    if [[ $(uname -s) == 'Darwin' ]]; then
        local ioption='-i.bak'
    else
        local ioption='-i'
    fi

    # set up testing suite if it doesn't yet exist
    if [ ! -d $WP_TESTS_DIR ]; then
        # set up testing suite
        mkdir -p $WP_TESTS_DIR
        echo "Cloning WordPress test suite from https://github.com/WordPress/wordpress-develop.git..."
        git clone --depth=1 https://github.com/WordPress/wordpress-develop.git /tmp/wordpress-tests-lib
        echo "Moving test files to $WP_TESTS_DIR..."
        mv /tmp/wordpress-tests-lib/tests/phpunit/includes $WP_TESTS_DIR/
        mv /tmp/wordpress-tests-lib/tests/phpunit/data $WP_TESTS_DIR/
        # Ensure correct permissions for www-data user
        echo "Setting permissions..."
        chown -R www-data:www-data $WP_TESTS_DIR
        chmod -R 755 $WP_TESTS_DIR
        # Cleaning up...
        rm -rf /tmp/wordpress-tests-lib
        echo "Verifying test suite installation..."
        if [ ! -d "$WP_TESTS_DIR/includes" ] || [ ! -f "$WP_TESTS_DIR/includes/functions.php" ]; then
            echo "Test suite files not found in $WP_TESTS_DIR/includes"
            ls -la $WP_TESTS_DIR/includes
            exit 1
        fi
    fi

    if [ ! -f wp-tests-config.php ]; then
        download https://develop.svn.wordpress.org/tags/$WP_VERSION/wp-tests-config-sample.php "$WP_TESTS_DIR"/wp-tests-config.php
        # remove all forward slashes in the end
        WP_CORE_DIR=$(echo $WP_CORE_DIR | sed "s:/\+$::")
        sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR"/wp-tests-config.php
        sed $ioption "s:__DIR__ . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR"/wp-tests-config.php
        sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR"/wp-tests-config.php
        sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR"/wp-tests-config.php
        sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR"/wp-tests-config.php
        sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR"/wp-tests-config.php
        
        # Ensure config file has correct permissions
        chown www-data:www-data "$WP_TESTS_DIR"/wp-tests-config.php
        chmod 644 "$WP_TESTS_DIR"/wp-tests-config.php
    fi
}

# Create test database if it doesn't exist
if [ "$SKIP_DB_CREATE" = "false" ]; then
    mysqladmin create $DB_NAME --user=$DB_USER --password=$DB_PASS --host=$DB_HOST --protocol=tcp || true
fi

# Update the error message in WordPress's bootstrap.php
if [ -f "$WP_TESTS_DIR/includes/bootstrap.php" ]; then
    # Create a temporary file for the sed command
    TEMP_FILE=$(mktemp)
    # Use a different delimiter (|) since the paths contain slashes
    sed "s|echo 'Error: wp-tests-config.php is missing! Please use wp-tests-config-sample.php to create a config file.' . PHP_EOL;|echo 'Error: wp-tests-config.php is missing from ' . \$config_file_path . '!' . PHP_EOL . 'Please run bin/install-wp-tests.sh to set up the testing environment.' . PHP_EOL;|" "$WP_TESTS_DIR/includes/bootstrap.php" > "$TEMP_FILE"
    mv "$TEMP_FILE" "$WP_TESTS_DIR/includes/bootstrap.php"
fi

install_test_suite
echo "WordPress test suite installed at $WP_TESTS_DIR"
