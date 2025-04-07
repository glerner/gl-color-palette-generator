#!/bin/bash
#
# Toggle comment status of specified lines in .gitignore
#
# Usage: ./bin/gitignore-toggle.sh [on|off]
#   on  - Comment out the lines (allow access to files)
#   off - Uncomment the lines (block access to files)
# (Windsurf Bug: can't edit files that are blocked by .gitignore)

# Define the gitignore file path - works whether run from root or bin directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [[ "$(basename "$SCRIPT_DIR")" == "bin" ]]; then
    GITIGNORE_FILE="$SCRIPT_DIR/../.gitignore"
else
    GITIGNORE_FILE="$SCRIPT_DIR/.gitignore"
fi

# Define the patterns to look for
PATTERNS=(
    "/source_files.txt"
    "/code_issues.md"
    "/untested_files.txt"
    "/temp_unanalyzed.txt"
    "test_processing_progress.txt"
    "/test_processing_scripts/"
    "interface_fixes_results.txt"
    "interface_map.txt"
    "last_processed_interface_line.txt"
    "test_files_to_analyze.txt"
    "last_processed_line.txt"
    "current_batch.txt"
    "junk.txt"
)

# Verify the .gitignore file exists
if [[ ! -f "$GITIGNORE_FILE" ]]; then
    echo "ERROR: .gitignore file not found at $GITIGNORE_FILE"
    exit 1
fi

# Check command line argument
if [[ "$1" != "on" && "$1" != "off" ]]; then
    echo "Usage: $0 [on|off]"
    echo "  on  - Comment out the lines (allow access to files)"
    echo "  off - Uncomment the lines (block access to files)"
    exit 1
fi

# Process the file
temp_file=$(mktemp)
changes_made=false

while IFS= read -r line; do
    modified_line="$line"

    # Check if this line matches any of our patterns
    for pattern in "${PATTERNS[@]}"; do
        # For "on" - add comment if line matches pattern exactly
        if [[ "$1" == "on" && "$line" == "$pattern" ]]; then
            modified_line="# $pattern"
            changes_made=true
            echo "Commenting: $line -> $modified_line"
            break
        fi

        # For "off" - remove comment if line is a commented pattern
        if [[ "$1" == "off" && "$line" == "# $pattern" ]]; then
            modified_line="$pattern"
            changes_made=true
            echo "Uncommenting: $line -> $modified_line"
            break
        fi
    done

    # Write the line to temp file
    echo "$modified_line" >> "$temp_file"
done < "$GITIGNORE_FILE"

# Update the file if changes were made
if [[ "$changes_made" == "true" ]]; then
    mv "$temp_file" "$GITIGNORE_FILE"
    echo "Updated .gitignore file"
else
    rm "$temp_file"
    echo "No changes needed - .gitignore already in requested state"
fi
