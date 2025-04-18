#!/bin/bash

# Directory to scan
TEST_DIR="tests"

# Output file for results
OUTPUT_FILE="docs/code-analysis/duplicate_classes.txt"

# Create output directory if it doesn't exist
mkdir -p "docs/code-analysis"

echo "Scanning for duplicate class declarations in $TEST_DIR..."
echo "Results will be saved to $OUTPUT_FILE"

# Clear previous results
> "$OUTPUT_FILE"

# Find all PHP files
PHP_FILES=$(find "$TEST_DIR" -name "*.php")

# Extract class names and their file paths
echo "Extracting class names from PHP files..."
declare -A class_files
for file in $PHP_FILES; do
    # Extract class declarations using grep and sed
    classes=$(grep -E "^class\s+[a-zA-Z0-9_]+" "$file" | sed -E 's/^class\s+([a-zA-Z0-9_]+).*/\1/')
    
    # Store each class with its file
    for class in $classes; do
        if [[ -n "${class_files[$class]}" ]]; then
            class_files[$class]="${class_files[$class]},$file"
        else
            class_files[$class]="$file"
        fi
    done
done

# Check for duplicates and write to output file
echo "Checking for duplicate class declarations..."
echo "Duplicate Class Declarations" > "$OUTPUT_FILE"
echo "===========================" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"

duplicate_found=false

for class in "${!class_files[@]}"; do
    IFS=',' read -ra files <<< "${class_files[$class]}"
    if [[ ${#files[@]} -gt 1 ]]; then
        duplicate_found=true
        echo "Class: $class" >> "$OUTPUT_FILE"
        echo "Found in files:" >> "$OUTPUT_FILE"
        for file in "${files[@]}"; do
            echo "  - $file" >> "$OUTPUT_FILE"
        done
        echo "" >> "$OUTPUT_FILE"
    fi
done

if $duplicate_found; then
    echo "Duplicate classes found! Check $OUTPUT_FILE for details."
    cat "$OUTPUT_FILE"
else
    echo "No duplicate classes found."
    echo "No duplicate classes found." >> "$OUTPUT_FILE"
fi

echo "Analysis complete."
