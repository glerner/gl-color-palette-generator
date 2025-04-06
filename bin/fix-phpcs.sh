#!/bin/bash

# Script to automatically fix PHPCS issues in files
# Usage: ./bin/fix-phpcs.sh [file or directory path]
# If no argument is provided, the script will process all custom code directories

# Check if vendor/bin/phpcbf exists
if [ ! -f "./vendor/bin/phpcbf" ]; then
    echo "PHPCBF not found in ./vendor/bin/"
    echo "Make sure you have installed PHP_CodeSniffer via Composer"
    echo "Run: composer require --dev squizlabs/php_codesniffer"
    exit 1
fi

# Function to run PHPCBF on a specific path
run_phpcbf() {
    local target=$1
    local description=$2
    
    echo "---------------------------------------------"
    echo "Processing: $target"
    echo "Description: $description"
    echo "---------------------------------------------"
    
    ./vendor/bin/phpcbf "$target"
    
    local exit_code=$?
    
    if [ $exit_code -eq 0 ]; then
        echo "✅ All fixable coding standards issues have been addressed in $target!"
    elif [ $exit_code -eq 1 ]; then
        echo "⚠️ Some files were fixed in $target but some issues remain that couldn't be fixed automatically."
    elif [ $exit_code -eq 2 ]; then
        echo "❌ PHPCBF encountered errors while processing $target."
    fi
    
    echo ""
    return $exit_code
}

# If a specific path is provided, only process that path
if [ "$1" != "" ]; then
    TARGET_PATH=$1
    echo "Running PHPCBF on: $TARGET_PATH"
    echo "Using configuration from phpcs.xml"
    
    ./vendor/bin/phpcbf "$TARGET_PATH"
    
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
fi

# If no argument is provided, process all custom code directories
echo "No specific path provided. Processing all custom code directories..."
echo "Using configuration from phpcs.xml"

# Process all custom directories
run_phpcbf "./assets" "Frontend assets (CSS, JS, images)"
run_phpcbf "./bin" "Custom scripts and tools for development and maintenance"
run_phpcbf "./docs" "Documentation files for the plugin"
run_phpcbf "./includes" "Core PHP classes and functions for the plugin"
run_phpcbf "./languages" "Translation and localization files"
run_phpcbf "./scripts" "Additional scripts for frontend or build processes"
run_phpcbf "./src" "Main source code for the plugin"
run_phpcbf "./templates" "Template files for frontend rendering"
run_phpcbf "./test_processing_scripts" "Scripts for processing and organizing tests"
run_phpcbf "./tests" "Test files for unit, integration, and WP-Mock tests"

# Process main plugin files
run_phpcbf "./gl-color-palette-generator.php" "Main plugin file"
run_phpcbf "./uninstall.php" "Plugin uninstallation file"

echo "---------------------------------------------"
echo "PHPCBF processing complete for all directories"
echo "---------------------------------------------"

exit 0
