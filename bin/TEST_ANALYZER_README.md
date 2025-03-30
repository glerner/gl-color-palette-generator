# Test Analyzer Tool

This tool helps analyze test files to determine their correct type (unit, wp-mock, or integration) and identify files that need to be moved to the correct directory.

## Prerequisites

You've already created two important files:
- `source_files.txt`: List of all PHP source files in the plugin (excluding tests and vendor)
- `test_files_to_analyze.txt`: List of test files to analyze (excluding system test files)

## How to Use

1. Make the script executable:
   ```bash
   chmod +x bin/test-analyzer.sh
   ```

2. Run the script:
   ```bash
   ./bin/test-analyzer.sh
   ```

3. Use the menu options:
   - Option 1: Process the next batch of test files
   - Option 2: Generate a move script
   - Option 3: Show statistics
   - Option 4: Check for untested files
   - Option 5: View bugs/issues found
   - Option 6: Exit

## Workflow

1. Select option 1 to get the next batch of test files to analyze
2. Have the AI assistant analyze these files
3. The AI will add entries to `test_analysis_results.txt` in this format:
   ```
   DECISION:/path/to/test-file.php:unit|wp-mock|integration:Reason for decision
   MOVE:/path/to/source/test-file.php:/path/to/target/test-file.php (if needed)
   ```

4. The AI will also document any bugs or issues found in `code_issues.md` in this format:
   ```
   BUG:/path/to/file.php:severity(high|medium|low):issue_type:Description of the issue
   ```
   
   Examples of issue types include:
   - wrong_namespace
   - incorrect_function_name
   - syntax_error
   - missing_dependency
   - incorrect_extends
   - incorrect_use_statement
4. Repeat until all files are analyzed
5. Select option 2 to generate a move script
6. Review and execute the move script to reorganize test files

## Files Created

- `test_analysis_results.txt`: Contains analysis results and decisions
- `move_test_files.sh`: Script to move files to their correct locations
- `untested_files.txt`: List of source files without corresponding tests
- `last_processed_line.txt`: Tracks progress through the test files
- `code_issues.md`: Markdown file documenting bugs and issues found during analysis

## Notes

- This tool does not modify any actual test files
- It only helps track the analysis process and generate commands
- You'll need to execute the move script separately after reviewing it

## Testing Structure Files (Excluded from Analysis)

The following files are part of the testing framework structure and should not be modified or moved:

### Base Test Classes

- `/tests/base/class-integration-test-case.php` - Base class for integration tests
- `/tests/base/class-unit-test-case.php` - Base class for unit tests
- `/tests/base/class-wp-mock-test-case.php` - Base class for WP-Mock tests
- `/tests/base/class-test-helpers.php` - Helper functions for tests
- `/tests/base/class-test-printer.php` - Custom test result printer

### Bootstrap Files

- `/tests/bootstrap.php` - Main bootstrap file
- `/tests/bootstrap/common.php` - Common bootstrap functionality
- `/tests/bootstrap/integration.php` - Bootstrap for integration tests
- `/tests/bootstrap/unit.php` - Bootstrap for unit tests
- `/tests/bootstrap/wp.php` - WordPress-specific bootstrap
- `/tests/bootstrap/wp-functions.php` - WordPress function mocks
- `/tests/bootstrap/wp-mock.php` - WP-Mock setup

### Documentation

- `/tests/TEST-PLAN.md` - Test plan documentation

### Index Files

- Various `index.php` files in test directories (placeholder files)

These files provide the framework and infrastructure for tests to run, but they don't test specific code in the plugin. They are automatically excluded from analysis by the script.
