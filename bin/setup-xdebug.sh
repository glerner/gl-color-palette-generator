#!/bin/bash

echo "Setting up Xdebug..."

# Check if Xdebug is installed
if ! php -m | grep -q xdebug; then
    echo "Xdebug not found. Installing..."
    sudo apt-get update
    sudo apt-get install -y php8.1-xdebug
fi

# Create xdebug.ini if it doesn't exist in PHP's conf.d directory
XDEBUG_INI="/etc/php/8.1/cli/conf.d/20-xdebug.ini"
if [ ! -f "$XDEBUG_INI" ]; then
    echo "Creating Xdebug configuration..."
    sudo cp "$(dirname "$0")/../xdebug.ini" "$XDEBUG_INI"
fi

# Create xdebug log file if it doesn't exist
if [ ! -f /tmp/xdebug.log ]; then
    sudo touch /tmp/xdebug.log
fi

# Set permissions
sudo chmod 666 /tmp/xdebug.log

# Restart PHP-FPM if it's running
if systemctl is-active --quiet php8.1-fpm; then
    sudo systemctl restart php8.1-fpm
fi

# Verify Xdebug installation
echo "Verifying Xdebug installation..."
php -v | grep -i xdebug

# Test Xdebug logging
echo "Testing Xdebug logging..."
php -r "var_dump(extension_loaded('xdebug'));"

echo "Xdebug setup complete!"
echo "Log file location: /tmp/xdebug.log"
echo "To view the log in real-time, run: tail -f /tmp/xdebug.log"
