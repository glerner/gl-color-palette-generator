#!/bin/bash
#
# Script to move and fix provider live API tests
#

echo "Moving and fixing provider live API tests..."

# Create integration/providers directory if it doesn't exist
mkdir -p /home/george/sites/gl-color-palette-generator/tests/integration/providers

# List of provider live API tests to move and fix
PROVIDER_TESTS=(
  "huggingface"
  "cohere"
  "palm"
  "azure-openai"
  "anthropic"
  "openai"
)

for provider in "${PROVIDER_TESTS[@]}"; do
  SOURCE_FILE="/home/george/sites/gl-color-palette-generator/tests/unit/providers/test-${provider}-provider-live-api.php"
  TARGET_FILE="/home/george/sites/gl-color-palette-generator/tests/integration/providers/test-${provider}-provider-live-api.php"
  
  if [ -f "$SOURCE_FILE" ]; then
    echo "Processing $provider provider live API test..."
    
    # Create target directory if it doesn't exist
    mkdir -p "$(dirname "$TARGET_FILE")"
    
    # Use git mv instead of regular mv for better git history tracking
    git mv "$SOURCE_FILE" "$TARGET_FILE"
    
    # Update namespace
    sed -i 's/namespace GL_Color_Palette_Generator\\Tests\\Unit\\Providers;/namespace GL_Color_Palette_Generator\\Tests\\Integration\\Providers;/g' "$TARGET_FILE"
    
    # Update @subpackage in docblock
    sed -i 's/@subpackage Tests\\Integration/@subpackage Tests\\Integration\\Providers/g' "$TARGET_FILE"
    
    # Update base class import if Test_Provider_Integration is used
    if grep -q "use GL_Color_Palette_Generator\\\\Tests\\\\Test_Provider_Integration;" "$TARGET_FILE"; then
      # Check if we need to add the proper base class
      if ! grep -q "use GL_Color_Palette_Generator\\\\Tests\\\\Base\\\\Integration_Test_Case;" "$TARGET_FILE"; then
        # Add the proper import after the namespace line
        sed -i '/namespace/a use GL_Color_Palette_Generator\\Tests\\Base\\Integration_Test_Case;' "$TARGET_FILE"
      fi
    fi
    
    echo "Updated $TARGET_FILE"
  else
    echo "Warning: $SOURCE_FILE not found, skipping"
  fi
done

echo "Provider live API test fixes complete!"
echo "NOTE: Original files have been moved to the integration tests directory and updated."
