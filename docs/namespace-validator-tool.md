# PHP Namespace Validation and Correction Tool

## Overview

This document outlines a PHP-based tool for systematically identifying and fixing namespace-related issues throughout the GL Color Palette Generator plugin. The tool will build on the methodical approach used in existing scripts that generated `test_analysis_results.txt` and `interface_fixes_results.txt`.

### What "Namespace Validation" Includes

When discussing "namespace validation," we're looking at the entire system of how classes are organized, referenced, and imported across the codebase. This includes problems with any of these three components:

1. **Namespace Declarations**: Ensuring the `namespace` statement correctly reflects the file's location in the directory structure
2. **Use Statements**: Verifying that `use` statements correctly import classes from their actual locations
3. **Class Declarations**: Confirming that class names match their expected namespaces and follow consistent naming patterns

All three components are interconnected in PHP's namespace system and must be consistent for the code to function properly.

## Purpose

- Identify incorrect or missing namespace imports across the codebase
- Generate a comprehensive report of namespace issues
- Provide clear suggestions for fixing each issue
- Allow for batch processing to manage the workload
- Document both issues and fixes for tracking progress

## Implementation Plan

### 1. File Discovery

The tool will first identify all PHP files in the project that need to be analyzed:

- Recursively scan the project directory for PHP files
- Exclude vendor directories, node_modules, and other non-project code
- Build a complete list of files to analyze
- Support for filtering files by directory or pattern

### 2. Batch Processing

To make the task manageable:

- Analyze files in configurable batches (default: 10 files per batch)
- Document issues in the results files without modifying PHP files
- Save analysis progress after each batch
- Allow resuming analysis from a specific batch
- Track which files have been analyzed

### 3. Namespace and Use Statement Analysis

For each file, the tool will:

- Parse the PHP file to extract the namespace declaration
- Identify all `use` statements
- Determine if the referenced classes/interfaces exist in the project
- Check if the namespace is correct based on the actual location of the referenced class
- Flag any issues found (incorrect namespace, missing class, etc.)

### 4. Namespace Mapping

The tool will build a comprehensive map of:

- All classes and interfaces in the project
- Their correct namespaces
- Alternative locations where similar class names exist
- This map will be used to suggest corrections

### 5. Correction Suggestions

For each issue found, the tool will:

- Suggest the correct namespace based on the namespace map
- Handle ambiguous cases where multiple options exist
- Provide context about why the suggestion is being made

### 6. Results Tracking

Results will be saved in two formats:

#### namespace_fixes_results.txt
A machine-readable format similar to existing results files:

```
file.php: No namespace issues found
another-file.php: INCORRECT_USE:GL_Color_Palette_Generator\Color_Palette:SHOULD_BE:GL_Color_Palette_Generator\Color_Management\Color_Palette
third-file.php: AMBIGUOUS_USE:GL_Color_Palette_Generator\Color_Constants:OPTIONS:[GL_Color_Palette_Generator\Constants\Color_Constants,GL_Color_Palette_Generator\Types\Color_Constants]
```

#### namespace_fixes_plan.md
A human-readable format for planning fixes:

```markdown
# Namespace Fixes Plan

## Files with No Issues
- file1.php
- file2.php

## Files Requiring Fixes
- file3.php:
  - Line 5: `use GL_Color_Palette_Generator\Color_Palette;`
  - Should be: `use GL_Color_Palette_Generator\Color_Management\Color_Palette;`

## Ambiguous Cases (Manual Review Required)
- file4.php:
  - Line 7: `use GL_Color_Palette_Generator\Color_Constants;`
  - Options:
    - `use GL_Color_Palette_Generator\Constants\Color_Constants;`
    - `use GL_Color_Palette_Generator\Types\Color_Constants;`
```

## Command Line Interface

```
Usage: php namespace-validator.php [options]

Options:
  --root=DIR         Root directory to scan (default: current directory)
  --batch-size=N     Number of files to process per batch (default: 10)
  --resume=N         Resume from batch number N
  --fix              Apply suggested fixes automatically
  --dry-run          Show what would be changed without making changes
  --output=FILE      Output file for results (default: namespace_fixes_results.txt)
  --plan=FILE        Output file for fix plan (default: namespace_fixes_plan.md)
  --exclude=DIR,DIR  Comma-separated list of directories to exclude
```

## Workflow Integration

### How to Use with Existing Analysis Workflows

1. Run the namespace validator tool to generate the initial reports
2. Review the `namespace_fixes_plan.md` file
3. Address issues in batches, similar to how you're working through `test_analysis_results.txt`
4. For ambiguous cases, manually review the code to determine the correct namespace
5. Update the plan file to track progress
6. Re-run the tool periodically to validate fixes and identify any new issues

## Two-Phase Approach to Code Quality

### Phase 1: Namespace Consistency

The primary goal of this tool is to address namespace consistency issues first, before attempting any other code quality improvements:

1. **Focus on Namespace Consistency**: Fix all namespace-related issues to ensure classes can be properly referenced.
2. **Validate with Static Analysis**: Use PHPStan or similar tools to verify that class names and interface names are consistent.
3. **Minimize Complexity**: By focusing only on namespace issues, you reduce the complexity of changes and make debugging easier.
4. **Maintain Clear Git History**: Separate namespace fixes from other changes for better version control tracking.
5. **Enable Incremental Testing**: Test each batch of namespace fixes to ensure they don't introduce new issues.

### Phase 2: WordPress Naming Conventions (Future Work)

After your namespace issues are resolved and the plugin is passing PHPStan (or similar static analysis tools) tests for class names and interface names being consistent:

1. Create a new tool to identify classes that don't follow WordPress naming conventions.
2. Develop a systematic plan for renaming classes, similar to your namespace fix plan.
3. Address these changes in batches, with thorough testing between batches.
4. Update documentation to reflect the new class names.

This two-phase approach ensures that you're not trying to solve too many problems at once, which could lead to confusion and errors.

### Recommended Process

1. Start with a small batch of files to validate the tool's suggestions
2. Focus on fixing one type of issue at a time (e.g., all Color_Palette references first)
3. After each batch of fixes, run tests to ensure functionality is maintained
4. Document any patterns or recurring issues for future reference
5. Update the tool as needed based on findings

## Implementation Details

### Core Components

1. **FileDiscovery**: Finds all PHP files to analyze
2. **NamespaceMapper**: Builds a map of classes and their correct namespaces
3. **UseStatementAnalyzer**: Analyzes use statements for correctness
4. **CorrectionSuggester**: Generates suggestions for fixing issues
5. **ResultsTracker**: Tracks and reports results
6. **BatchProcessor**: Manages processing files in batches

### Key Functions

```php
// Find all PHP files in the project
function findAllPhpFiles($rootDirectory, $excludeDirs = [])

// Build a map of classes and their namespaces
function buildNamespaceMap($phpFiles)

// Analyze use statements in a file
function analyzeUseStatements($filePath, $namespaceMap)

// Generate correction suggestions
function suggestCorrections($useStatement, $namespaceMap)

// Track and save results
function trackResults($results, $outputFile, $planFile)

// Process files in batches
function processBatches($files, $batchSize, $resumeBatch)
```

## Safety Measures

To ensure the tool doesn't cause issues:

1. Always create backups before applying any automatic fixes
2. Include a dry-run mode to preview changes
3. Validate syntax after any automatic changes
4. Include a rollback mechanism for failed fixes
5. Run tests after each batch of changes

## Benefits Over Bash Scripts

1. Better PHP parsing capabilities using PHP's tokenizer
2. More accurate namespace resolution
3. Stronger type checking and validation
4. Easier debugging and error handling
5. Unit testing capabilities for the tool itself
6. More sophisticated analysis of class relationships
7. Better handling of edge cases and complex namespaces

## Future Enhancements

1. Integration with IDE or editor plugins
2. Automatic fix application with version control integration
3. Visualization of namespace dependencies
4. Extended analysis for other PHP code quality issues
5. Configuration options for project-specific namespace conventions

## Conclusion

This tool will provide a systematic approach to fixing namespace issues throughout the codebase, similar to the existing test analysis workflow. By addressing these issues methodically, the codebase will become more consistent and maintainable, with proper namespace usage throughout.
