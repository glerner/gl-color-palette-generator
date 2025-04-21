#!/bin/bash

# Help text
show_help() {
    echo "Usage: $0 [options]"
    echo "Run PHPStan static analysis for GL Color Palette Generator"
    echo ""
    echo "Options:"
    echo "  --help          Show this help message"
    echo "  --level=N       Set PHPStan level (default: from phpstan.neon)"
    echo "  --fix           Show suggestions for fixing errors"
    echo "  --table         Display results in table format"
    echo "  --local         Run in local environment (default)"
    echo "  --lando         Run in Lando environment (requires sync-to-wp.sh first, then run in WordPress wp-content/plugins/gl-color-palette-generator/)"
    echo ""
}

# Default values
LEVEL=""
FIX=""
FORMAT="--error-format=table"
ENVIRONMENT="local"

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --help)
            show_help
            exit 0
            ;;
        --level=*)
            LEVEL="--level=${1#*=}"
            shift
            ;;
        --fix)
            FIX="--generate-baseline"
            shift
            ;;
        --table)
            FORMAT="--error-format=table"
            shift
            ;;
        --local)
            ENVIRONMENT="local"
            shift
            ;;
        --lando)
            ENVIRONMENT="lando"
            shift
            ;;
        *)
            echo "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

# Define paths
PLUGIN_SOURCE="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
FILESYSTEM_WP_ROOT="~/sites/wordpress"
PLUGIN_DEST="${FILESYSTEM_WP_ROOT}/wp-content/plugins/gl-color-palette-generator"

# Add color support
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Run PHPStan
if [ "$ENVIRONMENT" = "local" ]; then
    echo -e "${YELLOW}Running PHPStan in local environment...${NC}"
    cd "$PLUGIN_SOURCE"
    vendor/bin/phpstan analyse $LEVEL $FORMAT --no-progress $FIX

    # Check exit code
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}PHPStan analysis completed successfully!${NC}"
    else
        echo -e "${RED}PHPStan found issues.${NC}"
        exit 1
    fi
elif [ "$ENVIRONMENT" = "lando" ]; then
    echo -e "${YELLOW}Running PHPStan in Lando environment...${NC}"
    echo -e "${YELLOW}First, syncing files to WordPress...${NC}"

    # Run sync script
    bash "$PLUGIN_SOURCE/bin/sync-to-wp.sh"

    # Run PHPStan in Lando
    cd "$FILESYSTEM_WP_ROOT"
    lando ssh -c "cd /app/wp-content/plugins/gl-color-palette-generator && vendor/bin/phpstan analyse $LEVEL $FORMAT --no-progress $FIX"

    # Check exit code
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}PHPStan analysis completed successfully!${NC}"
    else
        echo -e "${RED}PHPStan found issues.${NC}"
        exit 1
    fi
fi
