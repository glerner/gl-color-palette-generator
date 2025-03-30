#!/bin/bash
#
# Script to fix test base class references in test files
#

echo "Fixing test base class references..."

# Create integration directory if it doesn't exist
mkdir -p /home/george/sites/gl-color-palette-generator/tests/integration/providers

# 1. Fix WP_Mock test cases
echo "Fixing WP_Mock test cases..."
find /home/george/sites/gl-color-palette-generator/tests/wp-mock -type f -name "*.php" -exec grep -l "\\\\WP_Mock\\\\Tools\\\\TestCase" {} \; | while read file; do
  echo "Updating $file"
  sed -i 's/\\WP_Mock\\Tools\\TestCase/GL_Color_Palette_Generator\\Tests\\Base\\WP_Mock_Test_Case/g' "$file"
  # Also fix the use statement if needed
  if ! grep -q "use GL_Color_Palette_Generator\\\\Tests\\\\Base\\\\WP_Mock_Test_Case;" "$file"; then
    sed -i '/^use/a use GL_Color_Palette_Generator\\Tests\\Base\\WP_Mock_Test_Case;' "$file"
  fi
done

# 2. Fix integration test cases with incorrect base class
echo "Fixing integration test base classes..."
find /home/george/sites/gl-color-palette-generator/tests/integration -type f -name "*.php" -exec grep -l "Test_Case_Integration" {} \; | while read file; do
  echo "Updating $file"
  # Fix the class extension
  sed -i 's/GL_Color_Palette_Generator\\Tests\\Test_Case_Integration/GL_Color_Palette_Generator\\Tests\\Base\\Integration_Test_Case/g' "$file"
  
  # Fix the use statement - more robust approach
  if grep -q "use GL_Color_Palette_Generator\\\\Tests\\\\Base" "$file"; then
    # Check if it's just the Base namespace or has a specific class
    if grep -q "use GL_Color_Palette_Generator\\\\Tests\\\\Base;" "$file"; then
      # Replace the Base namespace with the specific class
      sed -i 's/use GL_Color_Palette_Generator\\Tests\\Base;/use GL_Color_Palette_Generator\\Tests\\Base\\Integration_Test_Case;/g' "$file"
    else
      # Add the proper import if not already present
      if ! grep -q "use GL_Color_Palette_Generator\\\\Tests\\\\Base\\\\Integration_Test_Case" "$file"; then
        sed -i '/^namespace/a use GL_Color_Palette_Generator\\Tests\\Base\\Integration_Test_Case;' "$file"
      fi
    fi
  else
    # Add the import if not present at all
    sed -i '/^namespace/a use GL_Color_Palette_Generator\\Tests\\Base\\Integration_Test_Case;' "$file"
  fi
done

# 3. Fix WP_Mock_Test_Case without full namespace
echo "Fixing WP_Mock_Test_Case references without full namespace..."
find /home/george/sites/gl-color-palette-generator/tests/wp-mock -type f -name "*.php" -exec grep -l "extends WP_Mock_Test_Case" {} \; | while read file; do
  if ! grep -q "extends GL_Color_Palette_Generator\\\\Tests\\\\Base\\\\WP_Mock_Test_Case" "$file"; then
    echo "Updating $file"
    sed -i 's/extends WP_Mock_Test_Case/extends GL_Color_Palette_Generator\\Tests\\Base\\WP_Mock_Test_Case/g' "$file"
    # Also fix the use statement if needed
    if ! grep -q "use GL_Color_Palette_Generator\\\\Tests\\\\Base\\\\WP_Mock_Test_Case;" "$file"; then
      sed -i '/^use/a use GL_Color_Palette_Generator\\Tests\\Base\\WP_Mock_Test_Case;' "$file"
    fi
  fi
done

echo "Base class fixes complete!"
