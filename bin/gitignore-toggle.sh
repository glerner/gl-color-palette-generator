#!/bin/bash
#
# Toggle comment status of test processing files in .gitignore
# This allows temporarily accessing files that would otherwise be ignored
#
# Usage: ./gitignore-toggle.sh [on|off]
#   on  - Comment out the lines (allow access to files)
#   off - Uncomment the lines (block access to files)

set -e

# Define the gitignore file path
GITIGNORE_FILE="../.gitignore"

# Define the patterns to look for
PATTERNS=(
    "/test_analysis_results.txt"
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

# Simple direct approach to modify the .gitignore file
modify_gitignore() {
    local action=$1
    local temp_file=$(mktemp)
    local changes_made=false
    
    # Read the file line by line
    while IFS= read -r line; do
        local modified_line="$line"
        
        # Check if this line matches any of our patterns
        for pattern in "${PATTERNS[@]}"; do
            # Remove any leading # and whitespace for comparison
            local clean_line="${line#\# }"
            
            if [[ "$clean_line" == "$pattern" ]]; then
                if [[ "$action" == "off" && "$line" == "# $pattern" ]]; then
                    # Uncomment the line - files WILL be ignored by Git
                    modified_line="$pattern"
                    changes_made=true
                    echo "Uncommenting: $line -> $modified_line (will be ignored by Git)"
                elif [[ "$action" == "on" && "$line" == "$pattern" ]]; then
                    # Comment out the line - files will NOT be ignored by Git
                    modified_line="# $line"
                    changes_made=true
                    echo "Commenting out: $line -> $modified_line (will NOT be ignored by Git)"
                fi
                break
            fi
        done
        
        # Write the line (possibly modified) to the temp file
        echo "$modified_line" >> "$temp_file"
    done < "$GITIGNORE_FILE"
    
    # Only update the file if changes were made
    if [[ "$changes_made" == true ]]; then
        mv "$temp_file" "$GITIGNORE_FILE"
        echo "Updated .gitignore file"
    else
        rm "$temp_file"
        echo "No changes made to .gitignore file"
    fi
}

# Check if we're in the right directory
if [[ ! -f "$GITIGNORE_FILE" ]]; then
    echo "Error: .gitignore file not found at $GITIGNORE_FILE"
    echo "Make sure you're running this script from the bin directory"
    exit 1
fi

# Process command line arguments
if [[ "$1" == "on" ]]; then
    echo "===== GITIGNORE TOGGLE: ON ====="
    echo "Adding # to lines in .gitignore - Files will NOT be ignored by Git"
    echo "This allows access to test processing files for editing"
    modify_gitignore "on"
elif [[ "$1" == "off" ]]; then
    echo "===== GITIGNORE TOGGLE: OFF ====="
    echo "Removing # from lines in .gitignore - Files WILL be ignored by Git"
    echo "This blocks access to test processing files for editing"
    modify_gitignore "off"
else
    echo "Please specify 'on' or 'off'"
    echo "Usage: $0 [on|off]"
    echo "  on  - Comment out the lines (allow access to files)"
    echo "  off - Uncomment the lines (block access to files)"
    exit 1
fi

if [[ "$changes_made" == true ]]; then
    echo "✓ Successfully updated .gitignore settings"
    echo "✓ Run 'git check-ignore ./*.txt' to verify which files are now ignored"
else
    echo "ℹ️ No changes were needed - .gitignore already in the requested state"
fi

echo "Done!"
