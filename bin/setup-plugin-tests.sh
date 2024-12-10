#!/bin/bash

set -e  # Exit on any error

echo "=== Starting Plugin Test Environment Setup ==="

# Step 1: Install composer dependencies
echo "1. Installing Composer dependencies..."
cd /home/george/sites/gl-color-palette-generator
composer install
if [ $? -ne 0 ]; then
    echo "❌ Composer install failed"
    exit 1
fi
echo "✅ Composer dependencies installed"

# Step 2: Rebuild Lando environment
echo -e "\n2. Rebuilding Lando environment..."
cd /home/george/sites/wordpress
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
cd /home/george/sites/gl-color-palette-generator
bash bin/sync-to-wp.sh
if [ $? -ne 0 ]; then
    echo "❌ Plugin sync failed"
    exit 1
fi
echo "✅ Plugin synced to WordPress"

# Step 4: Install WordPress test suite
echo -e "\n4. Installing WordPress test suite..."
cd /home/george/sites/wordpress
lando install-wp-tests
if [ $? -ne 0 ]; then
    echo "❌ WordPress test suite installation failed"
    exit 1
fi
echo "✅ WordPress test suite installed"

# Step 5: Verify environment
echo -e "\n=== Verifying Environment ==="

# Check if WordPress is responding
echo "5a. Checking WordPress site..."
max_retries=5
retry_count=0
while [ $retry_count -lt $max_retries ]; do
    if curl -sI https://lc.lndo.site/ | grep "HTTP/"; then
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
echo -e "\n5b. Checking database connection..."
if ! lando mysql -e "SELECT 1;" >/dev/null 2>&1; then
    echo "❌ Database connection failed"
    exit 1
fi
echo "✅ Database is accessible"

# Check if PHPUnit is installed and working
echo -e "\n5c. Checking PHPUnit installation..."
cd /home/george/sites/wordpress/wp-content/plugins/gl-color-palette-generator
if ! lando composer exec phpunit -- --version; then
    echo "❌ PHPUnit check failed"
    exit 1
fi
echo "✅ PHPUnit is installed correctly"

echo -e "\n=== Setup Complete ==="
echo "You can now run tests with: lando test"
