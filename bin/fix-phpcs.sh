#!/bin/bash

# Script to automatically fix PHPCS issues in files
# Usage: ./bin/fix-phpcs.sh [file or directory path]

# Set default path to the project root if no argument provided
TARGET_PATH=${1:-.}

# Check if vendor/bin/phpcbf exists
if [ ! -f "./vendor/bin/phpcbf" ]; then
    echo "PHPCBF not found in ./vendor/bin/"
    echo "Make sure you have installed PHP_CodeSniffer via Composer"
    echo "Run: composer require --dev squizlabs/php_codesniffer"
    exit 1
fi

echo "Running PHPCBF on: $TARGET_PATH"
echo "Using configuration from phpcs.xml"

# Run PHPCBF with the project's phpcs.xml configuration
./vendor/bin/phpcbf "$TARGET_PATH"

# Check the exit code
EXIT_CODE=$?

if [ $EXIT_CODE -eq 0 ]; then
    echo "All fixable coding standards issues have been addressed!"
elif [ $EXIT_CODE -eq 1 ]; then
    echo "Some files were fixed but some issues remain that couldn't be fixed automatically."
    echo "You may need to fix these manually."
elif [ $EXIT_CODE -eq 2 ]; then
    echo "PHPCBF encountered errors while processing files."
fi

exit $EXIT_CODE
