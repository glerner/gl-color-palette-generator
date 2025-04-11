#!/bin/bash
# test-analyzer.sh - Analyzes test files to determine their correct type
# This script does not modify any files, it only helps track the analysis process

# Configuration
PROJECT_ROOT="/home/george/sites/gl-color-palette-generator"
TEST_ROOT="$PROJECT_ROOT/tests"
SOURCE_FILES="$PROJECT_ROOT/source_files.txt"
TEST_FILES="$PROJECT_ROOT/test_files_to_analyze.txt"
RESULTS_FILE="$PROJECT_ROOT/test_analysis_results.txt"
BUGS_FILE="$PROJECT_ROOT/code_issues.md"
MOVE_SCRIPT="$PROJECT_ROOT/move_test_files.sh"
BATCH_SIZE=5

# Function to get next batch of files
get_next_batch() {
    # Create a temporary file to store unanalyzed files
    TEMP_UNANALYZED="$PROJECT_ROOT/temp_unanalyzed.txt"

    # Find files that haven't been analyzed yet
    while IFS= read -r file; do
        # Check if this file has already been analyzed
        if ! grep -q "DECISION:$file:" "$RESULTS_FILE" 2>/dev/null; then
            echo "$file" >> "$TEMP_UNANALYZED"
        fi
    done < "$TEST_FILES"

    # If no unanalyzed files are found, report that
    if [ ! -s "$TEMP_UNANALYZED" ]; then
        echo "All files have been analyzed!"
        rm -f "$TEMP_UNANALYZED"
        return 1
    fi

    # Take the first BATCH_SIZE files from the unanalyzed list
    head -n "$BATCH_SIZE" "$TEMP_UNANALYZED"

    # Clean up
    rm -f "$TEMP_UNANALYZED"
    return 0
}

# Function to generate move script
# TODO: FUTURE ENHANCEMENT - The move script generation doesn't properly handle the format used in
# test_analysis_results.txt file. It's looking for "MOVE:" but the actual format uses "MOVE_NEEDED" at
# the end of DECISION lines. This needs to be updated to properly parse the source and target paths
# from the decision lines with the correct format.
# But am having AI analyze each file, instead of a script.
generate_move_script() {
    echo "#!/bin/bash" > "$MOVE_SCRIPT"
    echo "# Generated on $(date)" >> "$MOVE_SCRIPT"
    echo "# This script will move test files to their correct locations" >> "$MOVE_SCRIPT"
    echo "" >> "$MOVE_SCRIPT"

    grep "MOVE:" "$RESULTS_FILE" | while read -r line; do
        source=$(echo "$line" | cut -d':' -f2)
        target=$(echo "$line" | cut -d':' -f3)

        echo "# Moving $(basename "$source")" >> "$MOVE_SCRIPT"
        echo "mkdir -p \"$(dirname "$target")\"" >> "$MOVE_SCRIPT"
        echo "git mv \"$source\" \"$target\"" >> "$MOVE_SCRIPT"
        echo "" >> "$MOVE_SCRIPT"
    done

    chmod +x "$MOVE_SCRIPT"
    echo "Move script generated: $MOVE_SCRIPT"
}

# Function to show statistics
# TODO: FUTURE ENHANCEMENT - The statistics calculation doesn't properly handle different decision formats
# in the test_analysis_results.txt file (e.g., :CORRECT vs :OK, :EDIT_NEEDED vs :EDIT).
# This leads to inaccurate counts. The parsing logic should be made more flexible to handle
# variations in the format of decision markers.
show_stats() {
    # Create a clean temporary file for counting
    TEMP_COUNT_FILE="$(mktemp)"

    # Count test files and decisions
    total_tests=$(wc -l < "$TEST_FILES" | tr -d ' ')
    processed=0
    ok_files=0
    move_files=0
    edit_files=0
    edit_move_files=0
    bugs=0

    # Count DECISION entries if the file exists and has content
    if [ -s "$RESULTS_FILE" ]; then
        processed=$(grep -c "^DECISION:" "$RESULTS_FILE")
        ok_files=$(grep -c "^DECISION:.*:CORRECT$" "$RESULTS_FILE")
        move_files=$(grep -c "^DECISION:.*:MOVE_NEEDED$" "$RESULTS_FILE")
        edit_files=$(grep -c "^DECISION:.*:EDIT_NEEDED$" "$RESULTS_FILE")
        edit_move_files=$(grep -c "^DECISION:.*:EDIT_MOVE_NEEDED$" "$RESULTS_FILE")
    fi

    # Count bugs if the file exists and has content
    if [ -s "$BUGS_FILE" ]; then
        bugs=$(grep -c "^BUG:" "$BUGS_FILE")
    fi

    # Display statistics
    echo "Statistics:"
    echo "Total test files: $total_tests"
    echo "Processed files: $processed"
    echo "Files correctly located (OK): $ok_files"
    echo "Files needing relocation only (MOVE): $move_files"
    echo "Files needing edits only (EDIT): $edit_files"
    echo "Files needing both edits and relocation (EDIT-MOVE): $edit_move_files"
    echo "Bugs/issues found: $bugs"

    # Count test types
    unit_count=0
    wp_mock_count=0
    integration_count=0

    if [ -s "$RESULTS_FILE" ]; then
        unit_count=$(grep "^DECISION:" "$RESULTS_FILE" | grep -c ":unit:")
        wp_mock_count=$(grep "^DECISION:" "$RESULTS_FILE" | grep -c ":wp-mock:")
        integration_count=$(grep "^DECISION:" "$RESULTS_FILE" | grep -c ":integration:")
    fi

    # Show recommended distribution
    echo ""
    echo "Recommended distribution:"
    echo "Unit tests: $unit_count"
    echo "WP-Mock tests: $wp_mock_count"
    echo "Integration tests: $integration_count"

    # Show bug severity distribution if any bugs exist
    if [ -s "$BUGS_FILE" ] && [ "$bugs" -gt 0 ]; then
        high_count=$(grep "^BUG:" "$BUGS_FILE" | grep -c ":high:")
        medium_count=$(grep "^BUG:" "$BUGS_FILE" | grep -c ":medium:")
        low_count=$(grep "^BUG:" "$BUGS_FILE" | grep -c ":low:")

        echo ""
        echo "Bug severity distribution:"
        echo "High severity: $high_count"
        echo "Medium severity: $medium_count"
        echo "Low severity: $low_count"
    fi

    # Clean up
    rm -f "$TEMP_COUNT_FILE"
}

# Function to check for untested files
check_untested_files() {
    echo "Checking for untested files..."

    # Create a temporary file for untested files
    UNTESTED_FILES="$PROJECT_ROOT/untested_files.txt"
    > "$UNTESTED_FILES"

    # Create a temporary filtered source files list
    FILTERED_SOURCE_FILES="$PROJECT_ROOT/.filtered_source_files.tmp"
    > "$FILTERED_SOURCE_FILES"

    # Filter out files that don't need testing
    while IFS= read -r source_file; do
        # Skip files in .git/, .github/, node_modules/ directories and index.php files
        if [[ "$source_file" != *".git/"* && \
              "$source_file" != *".github/"* && \
              "$source_file" != */node_modules/* && \
              "$(basename "$source_file")" != "index.php" ]]; then
            echo "$source_file" >> "$FILTERED_SOURCE_FILES"
        fi
    done < "$SOURCE_FILES"

    # Check each filtered source file for a corresponding test
    while IFS= read -r source_file; do
        # Extract the base name without path and extension
        base_name=$(basename "$source_file" .php)

        # Look for corresponding test file
        if ! grep -q "test-$base_name.php" "$TEST_FILES" && ! grep -q "test-${base_name/-class/}.php" "$TEST_FILES"; then
            echo "$source_file" >> "$UNTESTED_FILES"
        fi
    done < "$FILTERED_SOURCE_FILES"

    # Clean up temporary file
    rm -f "$FILTERED_SOURCE_FILES"

    echo "Found $(wc -l < "$UNTESTED_FILES") potentially untested files."
    echo "List saved to: $UNTESTED_FILES"
}

# Main menu
main_menu() {
    while true; do
        echo ""
        echo "Test File Analyzer"
        echo "1. Process next batch of test files"
        echo "2. Generate move script"
        echo "3. Show statistics"
        echo "4. Check for untested files"
        echo "5. View bugs/issues found"
        echo "6. Show AI Prompt Again"
        echo "7. Exit"
        read -p "Select an option: " option

        case $option in
            1)
                # First display the instructions (only once per session)
                if [ ! -f "$PROJECT_ROOT/.prompt_shown" ]; then
                    echo ""
                    echo "Please analyze test files to determine:"
                    echo ""
                    echo "1. APPROPRIATE TEST TYPE:"
                    echo "   - Examine both the test file and the code it's testing"
                    echo "   - Determine if it should be a unit, wp-mock, or integration test based on:"
                    echo "     - Whether the code has WordPress dependencies"
                    echo "     - Whether those dependencies can be mocked or require a real WordPress environment"
                    echo "     - What base class the test extends and if that's appropriate"
                    echo "     - Proper base classes are in GL_Color_Palette_Generator\Tests\Base namespace:"
                    echo "       * Unit_Test_Case - For pure unit tests with no WordPress dependencies"
                    echo "       * WP_Mock_Test_Case - For tests that mock WordPress functions"
                    echo "       * Integration_Test_Case - For tests requiring a WordPress environment"
                    echo ""
                    echo "2. NAMESPACE AND DOCUMENTATION CORRECTNESS:"
                    echo "   - Check if the namespace matches the directory structure"
                    echo "   - Verify imports/use statements are correct"
                    echo "   - Test class naming should follow the Test_* pattern (e.g., Test_Ajax_Handler)"
                    echo "   - Base classes follow a different pattern (Unit_Test_Case, WP_Mock_Test_Case, Integration_Test_Case)"
                    echo "   - Base classes should be in the GL_Color_Palette_Generator\\Tests\\Base namespace"
                    echo "   - Verify file has proper docblocks with @package GL_Color_Palette_Generator annotation"
                    echo "   - Verify file has proper @subpackage annotation that matches directory structure"
                    echo "     (e.g., Tests\\Unit\\Classes for files in tests/unit/classes/)"
                    echo "   - Class-level and method-level docblocks should be present and descriptive"
                    echo ""
                    echo "3. TEST COVERAGE:"
                    echo "   - Verify the test is actually testing the intended code"
                    echo "   - Check for @covers annotations and their accuracy"
                    echo ""
                    echo "4. SCOPE ASSESSMENT:"
                    echo "   - Determine if the test is for functionality within the scope of a WordPress theme.json generator plugin"
                    echo "   - Flag potentially deprecated interfaces or classes with 'Also check if should be Deprecated, outside the scope of this plugin'"
                    echo "   - For clearly deprecated functionality, recommend removal with 'Consider removing this file as it contains tests for deprecated interfaces that are out of scope for the project'"
                    echo ""
                    echo "5. CODE ISSUES (optional):"
                    echo "   - If you notice any syntax errors or other issues, document them"
                    echo "   - Focus primarily on the test type, namespace, coverage, and scope"
                    echo ""
                    echo "For each file, add results to $RESULTS_FILE in this format:"
                    echo "DECISION:[file_path]:[unit|wp-mock|integration]:[detailed reason for decision including docblock status]:[OK|MOVE|EDIT|EDIT-MOVE]"
                    echo "MOVE:[source_path]:[target_path] (if the file needs to be moved)"
                    echo "EDIT:[file_path]:[description of changes needed] (if the file needs editing)"
                    echo ""
                    echo "Document any unexpected issues or bugs in $BUGS_FILE in this format:"
                    echo "BUG:[file_path]:[severity(high|medium|low)]:[issue_type]:[detailed description]"
                    echo "(Use this for issues unrelated to test type, namespace, or base class problems)"
                    echo ""

                    # Create a marker file to indicate the prompt has been shown
                    touch "$PROJECT_ROOT/.prompt_shown"
                fi

                # Get next batch of files
                if ! batch=$(get_next_batch); then
                    echo "All files have been processed."
                    continue
                fi

                if [ -z "$batch" ]; then
                    echo "No unanalyzed files found."
                    continue
                fi

                # Print the batch for analysis
                echo ""
                echo "Next batch of files to analyze:"
                echo "$batch"
                ;;
            2)
                generate_move_script
                ;;
            3)
                show_stats
                ;;
            4)
                check_untested_files
                ;;
            5)
                if [ -f "$BUGS_FILE" ] && [ -s "$BUGS_FILE" ]; then
                    echo "Bugs and issues found during analysis:"
                    cat "$BUGS_FILE"
                else
                    echo "No bugs or issues have been recorded yet."
                fi
                ;;
            6)
                # Show the AI prompt again
                # Remove the marker file so the prompt will be shown again
                rm -f "$PROJECT_ROOT/.prompt_shown"
                echo "AI prompt will be shown the next time you process a batch of files."
                ;;
            7)
                echo "Exiting."
                rm "$PROJECT_ROOT/.prompt_shown"
                exit 0
                ;;
            *)
                echo "Invalid option."
                ;;
        esac
    done
}

# Check if required input files exist
if [ ! -f "$SOURCE_FILES" ]; then
    echo "Error: Source files list not found at $SOURCE_FILES"
    echo "Please create this file with a list of source files to be tested using:"
    echo "find $PROJECT_ROOT/includes -name '*.php' > $SOURCE_FILES"
    exit 1
fi

if [ ! -f "$TEST_FILES" ]; then
    echo "Error: Test files list not found at $TEST_FILES"
    echo "Please create this file with a list of test files to analyze using:"
    echo "find $PROJECT_ROOT/tests -name 'test-*.php' | grep -v '/base/' | grep -v '/bootstrap/' > $TEST_FILES"
    exit 1
fi

# Initialize results file if it doesn't exist
if [ ! -f "$RESULTS_FILE" ]; then
    touch "$RESULTS_FILE"
    echo "# Test Analysis Results" > "$RESULTS_FILE"
    echo "# Format: DECISION:[file_path]:[unit|wp-mock|integration]:[detailed reason for decision]:[OK|MOVE|EDIT|EDIT-MOVE]" >> "$RESULTS_FILE"
    echo "# Created on $(date)" >> "$RESULTS_FILE"
    echo "" >> "$RESULTS_FILE"
fi

# Initialize bugs file if it doesn't exist
if [ ! -f "$BUGS_FILE" ]; then
    echo "# Code Issues Found During Test Analysis" > "$BUGS_FILE"
    echo "# Format: BUG:[file_path]:[severity]:[issue_type]:[description]" >> "$BUGS_FILE"
    echo "# Created on $(date)" >> "$BUGS_FILE"
    echo "" >> "$BUGS_FILE"
fi

# Start the main menu
main_menu
