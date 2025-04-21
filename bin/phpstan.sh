#!/bin/bash

# Help text
show_help() {
    echo "Usage: $0 [options] [paths...]"
    echo "Run PHPStan static analysis for GL Color Palette Generator"
    echo ""
    echo "Options:"
    echo "  --help               Show this help message"
    echo "  --level=N            Set PHPStan level (default: from phpstan.neon)"
    echo "  --fix                Show suggestions for fixing errors"
    echo "  --table              Display results in table format"
    echo "  --local              Run in local environment (default)"
    echo "  --lando              Run in Lando environment (requires sync-to-wp.sh first)"
    echo "  --memory-limit=SIZE  Set memory limit (default: 512M)"
    echo "  --all                Analyze all directories (default: only tests/)"
    echo ""
    echo "Examples:"
    echo "  $0                   Run analysis on tests/ directory only"
    echo "  $0 --all             Run analysis on all directories"
    echo "  $0 tests/unit/       Run analysis on specific directory"
    echo ""
}

# Default values
LEVEL=""
FIX=""
FORMAT="--error-format=table"
ENVIRONMENT="local"
MEMORY_LIMIT="--memory-limit=512M"
ANALYZE_ALL=false
PATHS=("tests/")

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
        --memory-limit=*)
            MEMORY_LIMIT="--memory-limit=${1#*=}"
            shift
            ;;
        --all)
            ANALYZE_ALL=true
            shift
            ;;
        --*)
            echo "Unknown option: $1"
            show_help
            exit 1
            ;;
        *)
            # If it's not an option, assume it's a path
            PATHS=("$1")
            shift
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
    
    # Check if local config exists, create if not
    if [ ! -f "phpstan.local.neon" ]; then
        echo -e "${YELLOW}Creating local PHPStan configuration without WordPress paths...${NC}"
        cp phpstan.neon phpstan.local.neon
        sed -i '/scanDirectories/,+3d' phpstan.local.neon
    fi
    
    # Determine paths to analyze
    if [ "$ANALYZE_ALL" = true ]; then
        ANALYZE_PATHS=()
    else
        ANALYZE_PATHS=("${PATHS[@]}")
    fi
    
    # Run PHPStan with memory limit
    echo -e "${YELLOW}Analyzing paths: ${ANALYZE_PATHS[@]:-all}${NC}"
    vendor/bin/phpstan analyse $LEVEL $FORMAT --no-progress $FIX $MEMORY_LIMIT --configuration=phpstan.local.neon ${ANALYZE_PATHS[@]}

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
    # Determine paths to analyze
    if [ "$ANALYZE_ALL" = true ]; then
        ANALYZE_PATHS=()
    else
        ANALYZE_PATHS=("${PATHS[@]}")
    fi
    
    # Run PHPStan in Lando with memory limit
    echo -e "${YELLOW}Analyzing paths: ${ANALYZE_PATHS[@]:-all}${NC}"
    lando ssh -c "cd /app/wp-content/plugins/gl-color-palette-generator && vendor/bin/phpstan analyse $LEVEL $FORMAT --no-progress $FIX $MEMORY_LIMIT ${ANALYZE_PATHS[@]}"

    # Check exit code
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}PHPStan analysis completed successfully!${NC}"
    else
        echo -e "${RED}PHPStan found issues.${NC}"
        exit 1
    fi
fi
