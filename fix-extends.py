#!/usr/bin/env python3
"""
Script to fix PHP class extends statements to use imported class names
instead of fully qualified names.

REASONING FOR THIS CHANGE:

PROBLEM: Using "fully" qualified class names in extends statements like:
  class Test_Rest_Controller_Accessibility extends GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case
Note that does not have a leading \, so is not a fully qualified name

This causes issues because:
1. PHP resolves the class name relative to the current namespace
2. This creates errors like "Undefined type GL_Color_Palette_Generator\Tests\WP_Mock\API\GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case"
3. It's less readable and harder to maintain

SOLUTION: Use imported class names instead:
  use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
  class Test_Rest_Controller_Accessibility extends WP_Mock_Test_Case

Benefits:
1. Cleaner, more readable code
2. Proper class resolution
3. Easier to update if class locations change
4. Centralizes dependencies at the top of the file

HOW PHP RESOLVES CLASS NAMES:
When PHP sees 'extends WP_Mock_Test_Case', it looks for the class in this order:
1. First, it checks the current namespace (e.g., GL_Color_Palette_Generator\Tests\WP_Mock\API)
2. Then, it checks all the imported classes via 'use' statements
3. Finally, it checks the global namespace if neither of the above found the class

In our case, it finds the class through the 'use' statement:
  use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;

This is why using the imported class name directly is more efficient and less error-prone.
When using a fully qualified name without a leading backslash, PHP tries to resolve it
relative to the current namespace, creating that strange concatenated namespace in the error.

This script:
1. Finds PHP files with fully qualified extends statements
2. Checks if the file already imports the base class
3. If it does, updates the extends statement to use the short name
4. If it doesn't, adds the import and updates the extends statement
"""

import os
import re
import sys
import subprocess
import tempfile
import traceback
import argparse

# Configuration
PLUGIN_NAMESPACE = "GL_Color_Palette_Generator"  # Root namespace of the plugin
BS = "\\"  # Backslash constant for better readability

# Global debug flag
DEBUG = False

def debug(message):
    """Print debug message only if DEBUG flag is enabled."""
    if DEBUG:
        print(message)

def find_php_files_with_extends(target_dir):
    """Find PHP files with fully qualified extends statements """
    matching_files = []

    # Pattern to match fully qualified extends statements
    # This will match both with and without leading backslash
    extends_pattern = re.compile(r'extends\s+([\\]?' + re.escape(PLUGIN_NAMESPACE) + r'\\[^{;\s]+)')

    # Walk through all files in the directory
    for root, _, files in os.walk(target_dir):
        for filename in files:
            if filename.endswith('.php'):
                file_path = os.path.join(root, filename)

                try:
                    with open(file_path, 'r', encoding='utf-8') as f:
                        content = f.read()

                        # Check if the file has a fully qualified extends statement
                        match = extends_pattern.search(content)
                        if match:
                            debug(f"DEBUG: Found match: {os.path.basename(file_path)}\n  Match: {match.group(0)}")
                            matching_files.append(file_path)
                except Exception as e:
                    debug(f"DEBUG: Error reading {file_path}: {e}")

    if matching_files:
        debug(f"DEBUG: Found {len(matching_files)} files with fully qualified extends")
        for f in matching_files:
            debug(f"  {os.path.basename(f)}")
        print(f"Found {len(matching_files)} files with fully qualified extends")
    else:
        debug("DEBUG: No files found with fully qualified extends")
        print("No files found with fully qualified extends")
    return matching_files

def extract_base_class_info(file_path):
    """Extract the base class name and namespace from the fully qualified name."""
    # Read the file content
    with open(file_path, 'r') as f:
        content = f.read()

    debug(f"DEBUG: Analyzing file: {file_path}")

    # First, find the class declaration line
    class_pattern = r"class\s+([A-Za-z0-9_]+)\s+extends\s+([^{;\s]+)"
    class_match = re.search(class_pattern, content)

    if not class_match:
        debug(f"DEBUG: No class match found in {file_path}")
        return None, None, None

    # Get the class name and fully qualified base class name
    class_name = class_match.group(1)
    fully_qualified_name = class_match.group(2)

    debug(f"DEBUG: Found class {class_name} extends {fully_qualified_name}")

    # Store the original extends statement for display
    original_extends = f"class {class_name} extends {fully_qualified_name}"

    # Check if it starts with a leading backslash and remove it
    if fully_qualified_name.startswith('\\'):
        debug(f"DEBUG: Removing leading backslash from {fully_qualified_name}")
        fully_qualified_name = fully_qualified_name[1:]

    # Check if it starts with our plugin namespace
    namespace_prefix = f"{PLUGIN_NAMESPACE}{BS}"
    debug(f"DEBUG: Checking if starts with {namespace_prefix}")
    if not fully_qualified_name.startswith(namespace_prefix):
        debug(f"DEBUG: Class does not start with plugin namespace")
        return None, None, None

    # Extract the namespace path and class name
    # Remove the plugin namespace prefix
    remaining_path = fully_qualified_name[len(namespace_prefix):]

    # Find the last backslash to separate namespace path from class name
    last_backslash = remaining_path.rfind(BS)

    if last_backslash == -1:
        # No additional namespace path, just the class name
        namespace_path = ""
        base_class_name = remaining_path
    else:
        # Split into namespace path and class name
        namespace_path = remaining_path[:last_backslash]
        base_class_name = remaining_path[last_backslash+1:]

    return namespace_path, base_class_name, original_extends

def check_use_statement_exists(content, full_class_path):
    """Check if the use statement for the class already exists in the content."""
    # Escape backslashes for regex
    escaped_path = re.escape(full_class_path)
    pattern = f"use {escaped_path};"
    
    result = re.search(pattern, content) is not None
    if result:
        debug(f"DEBUG: Use statement already exists")
    return result

def has_class_import(file_path, namespace_path, class_name):
    """Check if the file imports the class."""
    # Build the full class path
    namespace_separator = BS if namespace_path else ""
    full_class_path = f"{PLUGIN_NAMESPACE}\\{namespace_path}{namespace_separator}{class_name}"
    
    with open(file_path, 'r') as f:
        content = f.read()
    
    return check_use_statement_exists(content, full_class_path)

def get_namespace_line(file_path):
    """Extract the namespace line from a PHP file."""
    with open(file_path, 'r') as f:
        content = f.read()

    namespace_match = re.search(r'namespace\s+([^;]+);', content)
    if namespace_match:
        return namespace_match.group(0)
    return None

def update_extends_statement(file_path, namespace_path, class_name):
    """Replace the fully qualified extends with the short name and add use statement if needed."""
    with open(file_path, 'r') as f:
        content = f.read()

    debug(f"DEBUG: Updating extends statement ")
    debug(f"DEBUG: Looking for class_name: {class_name}, namespace_path: {namespace_path}")

    # Need to escape backslashes for regex
    escaped_namespace = re.escape(PLUGIN_NAMESPACE)
    escaped_path = re.escape(namespace_path)

    # For debugging, let's try a simpler approach first - find the actual extends statement
    class_pattern = r"class\s+([A-Za-z0-9_]+)\s+extends\s+([^{;\s]+)"
    class_match = re.search(class_pattern, content)

    if not class_match:
        debug(f"DEBUG: No class pattern match found")
        return False

    debug(f"DEBUG: Found extends statement: {class_match.group(0)}")

    # Create the replacement pattern based on what we actually found
    original_extends = class_match.group(0)
    new_extends = original_extends.replace(class_match.group(2), class_name)

    debug(f"DEBUG: Original extends: {original_extends}")
    debug(f"DEBUG: New extends: {new_extends}")
    
    # Build the full class path for the use statement
    namespace_separator = BS if namespace_path else ""
    full_class_path = f"{PLUGIN_NAMESPACE}{BS}{namespace_path}{namespace_separator}{class_name}"
    use_statement = f"use {full_class_path};"
    
    # Check if the use statement already exists
    use_pattern = re.escape(use_statement)
    has_use_statement = re.search(use_pattern, content) is not None
    
    # Prepare the new content with the updated extends statement
    new_content = content.replace(original_extends, new_extends)
    
    # If we need to add a use statement, insert it after namespace declaration if it exists
    if not has_use_statement:
        debug(f"DEBUG: Need to add use statement: {use_statement}")
        
        # Try to find the namespace declaration
        namespace_match = re.search(r'namespace\s+([^;]+);', new_content)
        
        if namespace_match:
            # Insert after namespace declaration
            namespace_line = namespace_match.group(0)
            debug(f"DEBUG: Found namespace line: {namespace_line}")
            new_content = new_content.replace(namespace_line, f"{namespace_line}\n{use_statement}")
            print(f"  Added: {use_statement}")
        else:
            # Tell programmer to add it manually
            print(f"  Please manually add: {use_statement}")

    if new_content == content:
        debug(f"DEBUG: No changes made to the content")
        return False

    debug(f"DEBUG: Writing updated content to {file_path}")

    with open(file_path, 'w') as f:
        f.write(new_content)

    return True

def suggest_use_statement(namespace_path, class_name):
    """Generate a suggested use statement for the class."""
    # Make sure the namespace path ends with a backslash if it's not empty
    namespace_separator = BS if namespace_path else ""
    use_statement = f"use {PLUGIN_NAMESPACE}\\{namespace_path}{namespace_separator}{class_name};"

    return use_statement

def main():
    # Set up argument parser
    parser = argparse.ArgumentParser(description='Fix PHP class extends statements to use imported class names.')
    parser.add_argument('target_directory', help='Directory containing PHP files to process')
    parser.add_argument('--debug', action='store_true', help='Enable debug output')
    
    args = parser.parse_args()
    
    # Set global debug flag
    global DEBUG
    DEBUG = args.debug
    
    target_dir = args.target_directory

    if not os.path.isdir(target_dir):
        print(f"Error: Directory {target_dir} does not exist")
        sys.exit(1)

    print(f"Processing PHP files in {target_dir}")
    if DEBUG:
        print("Debug mode enabled - detailed output will be shown")
    files = find_php_files_with_extends(target_dir)

    # No need to duplicate file count information
    modified_count = 0

    for file_path in files:
        # Get relative path from target_dir
        rel_path = os.path.relpath(file_path, target_dir)
        print(f"\nChecking {rel_path}")

        namespace_path, class_name, original_extends = extract_base_class_info(file_path)
        if not class_name:
            print("  Could not determine class name, skipping")
            continue

        # Check if use statement already exists
        has_import = has_class_import(file_path, namespace_path, class_name)
        
        # Generate the suggested use statement
        suggested_use = suggest_use_statement(namespace_path, class_name)

        # Update the extends statement
        if update_extends_statement(file_path, namespace_path, class_name):
            print(f"  Original: {original_extends}")
            print(f"  Updated: class ... extends {class_name}")
            
            # Check if we added a use statement (check again after update)
            with open(file_path, 'r') as f:
                content = f.read()
                
            namespace_separator = BS if namespace_path else ""
            full_class_path = f"{PLUGIN_NAMESPACE}\\{namespace_path}{namespace_separator}{class_name}"
            
            if not has_import and check_use_statement_exists(content, full_class_path):
                print(f"  Added: {suggested_use}")
            modified_count += 1
        else:
            print("  Failed to update extends statement")

    print(f"\nCompleted! Modified {modified_count} files.")

def global_exception_handler(exc_type, exc_value, exc_traceback):
    # Get the most recent frame from the traceback for location information
    tb_frame = traceback.extract_tb(exc_traceback)[-1] if exc_traceback else None

    # Extract file, line, and function information if available
    file_info = f" in {tb_frame.filename}:{tb_frame.lineno} (function: {tb_frame.name})" if tb_frame else ""

    # Create error message with location information
    error_msg = f"Unhandled exception: {exc_type.__name__}: {exc_value}{file_info}\n"

    # Always write to both stdout and stderr to maximize visibility
    sys.stdout.write(f"\n==== GLOBAL EXCEPTION HANDLER ====\n")
    sys.stdout.write(error_msg)
    sys.stdout.write("\nDetailed traceback:\n")
    sys.stdout.write(''.join(traceback.format_exception(exc_type, exc_value, exc_traceback)))
    sys.stdout.write("\nPlease report this error with the above information.\n")
    sys.stdout.write("==== END EXCEPTION HANDLER ====\n")
    sys.stdout.flush()

    # Also write to stderr which pytest will capture even with --capture=no
    sys.stderr.write(f"\n==== GLOBAL EXCEPTION HANDLER ====\n")
    sys.stderr.write(error_msg)
    sys.stderr.write("\nDetailed traceback:\n")
    sys.stderr.write(''.join(traceback.format_exception(exc_type, exc_value, exc_traceback)))
    sys.stderr.write("\nPlease report this error with the above information.\n")
    sys.stderr.write("==== END EXCEPTION HANDLER ====\n")
    sys.stderr.flush()

if __name__ == "__main__":
    # Install the exception handler
    sys.excepthook = global_exception_handler
    main()
