#!/bin/bash

# Script to fix PHP class extends statements to use imported class names
# instead of fully qualified names
#
# REASONING FOR THIS CHANGE:
#
# PROBLEM: Using "fully" qualified class names in extends statements like:
#   class Test_Rest_Controller_Accessibility extends GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case
# Note that does not have a leading \, so is not a fully qualified name
#
# This causes issues because:
# 1. PHP resolves the class name relative to the current namespace
# 2. This creates errors like "Undefined type GL_Color_Palette_Generator\Tests\WP_Mock\API\GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case"
# 3. It's less readable and harder to maintain
#
# SOLUTION: Use imported class names instead:
#   use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
#   class Test_Rest_Controller_Accessibility extends WP_Mock_Test_Case
#
# Benefits:
# 1. Cleaner, more readable code
# 2. Proper class resolution
# 3. Easier to update if class locations change
# 4. Centralizes dependencies at the top of the file
#
# HOW PHP RESOLVES CLASS NAMES:
# When PHP sees 'extends WP_Mock_Test_Case', it looks for the class in this order:
# 1. First, it checks the current namespace (e.g., GL_Color_Palette_Generator\Tests\WP_Mock\API)
# 2. Then, it checks all the imported classes via 'use' statements
# 3. Finally, it checks the global namespace if neither of the above found the class
#
# In our case, it finds the class through the 'use' statement:
#   use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
#
# This is why using the imported class name directly is more efficient and less error-prone.
# When using a fully qualified name without a leading backslash, PHP tries to resolve it
# relative to the current namespace, creating that strange concatenated namespace in the error.

# Set the directory to process
TARGET_DIR="$1"

if [ -z "$TARGET_DIR" ]; then
  echo "Usage: $0 <target_directory>"
  echo "Example: $0 /home/george/sites/gl-color-palette-generator/tests/wp-mock"
  exit 1
fi

# Check if the directory exists
if [ ! -d "$TARGET_DIR" ]; then
  echo "Error: Directory $TARGET_DIR does not exist"
  exit 1
fi

echo "Processing PHP files in $TARGET_DIR..."

# Find PHP files with the fully qualified extends pattern
FILES=$(grep -l "extends GL_Color_Palette_Generator" $(find "$TARGET_DIR" -name "*.php"))

# Counter for modified files
MODIFIED=0

for FILE in $FILES; do
  echo "Checking $FILE"

  # Extract the base class name from the fully qualified name
  BASE_CLASS=$(grep -o "extends GL_Color_Palette_Generator\\\\Tests\\\\Base\\\\[A-Za-z_]*" "$FILE" | sed 's/.*\\\\//')

  if [ -z "$BASE_CLASS" ]; then
    echo "  Could not determine base class, skipping"
    continue
  fi

  echo "  Base class: $BASE_CLASS"

  # Check if the file imports the base class
  if grep -q "use GL_Color_Palette_Generator\\\\Tests\\\\Base\\\\$BASE_CLASS" "$FILE"; then
    echo "  File imports the base class, updating extends statement"

    # Replace the fully qualified extends with the short name
    sed -i "s/extends GL_Color_Palette_Generator\\\\Tests\\\\Base\\\\$BASE_CLASS/extends $BASE_CLASS/g" "$FILE"

    # Verify the change
    if grep -q "extends $BASE_CLASS" "$FILE"; then
      echo "  Successfully updated"
      MODIFIED=$((MODIFIED + 1))
    else
      echo "  Failed to update"
    fi
  else
    echo "  File does not import the base class, adding import and updating extends"

    # Get the namespace line
    NAMESPACE_LINE=$(grep -n "^namespace" "$FILE" | cut -d: -f1)

    if [ -z "$NAMESPACE_LINE" ]; then
      echo "  Could not find namespace line, skipping"
      continue
    fi

    # Add the use statement after the namespace
    sed -i "${NAMESPACE_LINE}a\\
use GL_Color_Palette_Generator\\\\Tests\\\\Base\\\\$BASE_CLASS;" "$FILE"

    # Replace the fully qualified extends with the short name
    sed -i "s/extends GL_Color_Palette_Generator\\\\Tests\\\\Base\\\\$BASE_CLASS/extends $BASE_CLASS/g" "$FILE"

    # Verify the changes
    if grep -q "use GL_Color_Palette_Generator\\\\Tests\\\\Base\\\\$BASE_CLASS" "$FILE" && grep -q "extends $BASE_CLASS" "$FILE"; then
      echo "  Successfully updated with new import"
      MODIFIED=$((MODIFIED + 1))
    else
      echo "  Failed to update"
    fi
  fi

  echo ""
done

echo "Completed! Modified $MODIFIED files."
