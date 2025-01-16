#!/bin/bash

# Help text
show_help() {
    echo "Usage: $0 [options] [--file FILE]"
    echo "Run tests for GL Color Palette Generator"
    echo ""
    echo "Options:"
    echo "  --help          Show this help message"
    echo "  --unit          Run unit tests (providers, api, admin)"
    echo "  --mock          Run WP Mock tests"
    echo "  --integration   Run integration tests"
    echo "  --providers     Run provider tests"
    echo "  --api           Run API tests"
    echo "  --admin         Run admin tests"
    echo "  --core          Run core tests"
    echo "  --all           Run all tests"
    echo "  --coverage      Generate code coverage report"
    echo "  --file FILE     Run specific test file"
    echo ""
    echo "Advanced options (if needed):"
    echo "  --testsuite NAME    Run specific test suite"
    echo "  --group NAME        Run tests with specific group"
    echo "  --bootstrap FILE    Use specific bootstrap file"
    echo ""
    echo "Note: If tests are slow or failing, check for and clean large trace files."
    echo "Lando mounts the container's /var/www/ to your host system, so clean files"
    echo "directly on your host OS:"
    echo ""
    echo "Linux:"
    echo "  sudo rm /var/www/html/logs/trace.*.xt"
    echo "  sudo journalctl --vacuum-size=100M"
    echo "macOS:"
    echo "  sudo rm /var/www/html/logs/trace.*.xt"
    echo "  sudo rm /var/log/system.log.*"
    echo "Windows:"
    echo "  del C:\\var\\www\\html\\logs\\trace.*.xt"
    echo "  cleanmgr /sageset:65535"
    echo ""
    echo "Examples:"
    echo "  $0 --unit           # Run all unit tests"
    echo "  $0 --mock           # Run WP Mock tests"
    echo "  $0 --providers      # Run only provider tests"
    echo "  $0 --all           # Run all tests"
    echo "  $0 --mock --file tests/test-file.php  # Run specific test file"
}

# Add color support
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Cleanup function
cleanup() {
    # Remove temporary test files
    if [ -d "${PLUGIN_DIR}/build/coverage" ]; then
        rm -rf "${PLUGIN_DIR}/build/coverage"
    fi
}

# Register cleanup on script exit
trap cleanup EXIT

# Default values
PHPUNIT="vendor/bin/phpunit"
BOOTSTRAP=""
TESTSUITE=""
DIRECTORY=""
COVERAGE=""
GROUP=""
TEST_FILE=""
RUN_UNIT=false
RUN_MOCK=false
RUN_INTEGRATION=false

# Validate WP_ROOT is set
if [ -z "${WP_ROOT:-}" ]; then
    echo -e "${RED}Error:${NC} WP_ROOT environment variable must be set"
    echo "Usage examples:"
    echo "  Integration Tests (using WordPress test framework):"
    echo "  cd ~/sites/gl-color-palette-generator/ && \\"
    echo "  bash ./bin/sync-to-wp.sh && \\"
    echo "  cd ~/sites/wordpress/wp-content/plugins/gl-color-palette-generator && \\"
    echo "  WP_ROOT=/app bin/test.sh --integration"

    echo "Run a specific test file:"
    echo "  $0 --mock --file tests/wp-mock/color-management/test-ai-palette-generator.php"

    exit 1
fi

# Get absolute paths
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PLUGIN_DIR="$( cd "$SCRIPT_DIR/.." && pwd )"
TEST_DIR="$PLUGIN_DIR/tests"

echo -e "${GREEN}=== Starting test.sh ===${NC}"
echo "Using directories:"
echo "  Plugin directory: $PLUGIN_DIR"
echo "  Test directory: $TEST_DIR"

# Set WP_TESTS_DIR based on WP_ROOT
export WP_TESTS_DIR="${WP_ROOT}/wp-content/plugins/wordpress-develop/tests/phpunit"

echo "Checking required files and directories..."
echo "  WP_ROOT: ${WP_ROOT}"
echo "  WP_TESTS_DIR: ${WP_TESTS_DIR}"

# Check for wordpress-develop
if [ ! -d "${WP_ROOT}/wp-content/plugins/wordpress-develop" ]; then
    echo -e "${RED}Error:${NC} wordpress-develop not found at ${WP_ROOT}/wp-content/plugins/wordpress-develop"
    echo "Please run the test setup script:"
    echo "  cd ${WP_ROOT}"
    echo "  lando ssh -c \"cd wp-content/plugins/gl-color-palette-generator && bash ./bin/setup-plugin-tests.sh\""
    exit 1
fi

# Check for test files
if [ ! -f "${WP_TESTS_DIR}/includes/functions.php" ]; then
    echo -e "${RED}Error:${NC} WordPress test files not found at ${WP_TESTS_DIR}"
    echo "Required files:"
    echo "  - ${WP_TESTS_DIR}/includes/functions.php"
    echo "  - ${WP_TESTS_DIR}/includes/bootstrap.php"
    echo ""
    echo "Please run the test setup script:"
    echo "  cd ${WP_ROOT}"
    echo "  lando ssh -c \"cd wp-content/plugins/gl-color-palette-generator && bash ./bin/setup-plugin-tests.sh\""
    exit 1
fi

# Check for wp-tests-config.php
if [ ! -f "${WP_TESTS_DIR}/wp-tests-config.php" ]; then
    echo -e "${RED}Error:${NC} wp-tests-config.php not found at ${WP_TESTS_DIR}/wp-tests-config.php"
    echo "Please run the test setup script:"
    echo "  cd ${WP_ROOT}"
    echo "  lando ssh -c \"cd wp-content/plugins/gl-color-palette-generator && bash ./bin/setup-plugin-tests.sh\""
    exit 1
fi

# Check for composer dependencies
if [ ! -f "${PLUGIN_DIR}/vendor/bin/phpunit" ]; then
    echo -e "${RED}Error:${NC} PHPUnit not found. Please run the test setup script:"
    echo "  cd ${WP_ROOT}"
    echo "  lando ssh -c \"cd wp-content/plugins/gl-color-palette-generator && bash ./bin/setup-plugin-tests.sh\""
    exit 1
fi

if [ ! -f "${PLUGIN_DIR}/vendor/autoload.php" ]; then
    echo -e "${RED}Error:${NC} Composer autoloader not found. Please run the test setup script:"
    echo "  cd ${WP_ROOT}"
    echo "  lando ssh -c \"cd wp-content/plugins/gl-color-palette-generator && bash ./bin/setup-plugin-tests.sh\""
    exit 1
fi

# Parse arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --help)
            show_help
            exit 0
            ;;
        --unit)
            BOOTSTRAP="${TEST_DIR}/bootstrap/unit.php"
            TESTSUITE="unit"
            shift
            ;;
        --mock)
            BOOTSTRAP="${TEST_DIR}/bootstrap/wp-mock.php"
            TESTSUITE="wp-mock"
            shift
            ;;
        --integration)
            BOOTSTRAP="${TEST_DIR}/bootstrap/integration.php"
            TESTSUITE="integration"
            shift
            ;;
        --providers)
            BOOTSTRAP="${TEST_DIR}/bootstrap/unit.php"
            # Most provider tests are pure unit tests
            DIRECTORY="${TEST_DIR}/providers"
            shift
            ;;
        --api)
            BOOTSTRAP="${TEST_DIR}/bootstrap/wp-mock.php"
            # API tests need WordPress mocks
            DIRECTORY="${TEST_DIR}/api"
            shift
            ;;
        --admin)
            BOOTSTRAP="${TEST_DIR}/bootstrap/wp-mock.php"
            # Admin tests need WordPress mocks
            DIRECTORY="${TEST_DIR}/admin"
            shift
            ;;
        --core)
            BOOTSTRAP="${TEST_DIR}/bootstrap/unit.php"
            # Core tests should be pure unit tests
            DIRECTORY="${TEST_DIR}/core"
            shift
            ;;
        --all)
            # If --integration is also specified, use bootstrap/wp.php
            if [[ "$*" == *"--integration"* ]]; then
                BOOTSTRAP="${TEST_DIR}/bootstrap/wp.php"
            elif [[ "$*" == *"--mock"* ]]; then
                BOOTSTRAP="${TEST_DIR}/bootstrap/wp-mock.php"
            else
                BOOTSTRAP="${TEST_DIR}/bootstrap/unit.php"
                # Default to unit tests
            fi
            shift
            ;;
        --coverage)
            COVERAGE="--coverage-html build/coverage"
            shift
            ;;
        --testsuite)
            TESTSUITE="--testsuite $2"
            shift 2
            ;;
        --group)
            GROUP="--group $2"
            shift 2
            ;;
        --bootstrap)
            BOOTSTRAP="$2"
            shift 2
            ;;
        --file)
            TEST_FILE="$2"
            shift 2
            ;;
        *)
            echo -e "${RED}Unknown option:${NC} $1"
            show_help
            exit 1
            ;;
    esac
done

# Build command
CMD="$PHPUNIT"
[[ -n "$BOOTSTRAP" ]] && CMD="$CMD --bootstrap=$BOOTSTRAP"
[[ -n "$TESTSUITE" ]] && CMD="$CMD --testsuite $TESTSUITE"
[[ -n "$GROUP" ]] && CMD="$CMD $GROUP"
[[ -n "$COVERAGE" ]] && CMD="$CMD $COVERAGE"
[[ -n "$TEST_FILE" ]] && CMD="$CMD $TEST_FILE" || [[ -n "$DIRECTORY" ]] && CMD="$CMD $DIRECTORY"

# Add verbose output
CMD="$CMD --debug --verbose"

# Show test discovery info
echo -e "\n${GREEN}Looking for tests in (as defined in phpunit.xml):${NC}"
if [ "$TESTSUITE" == "integration" ]; then
    echo "  - ${TEST_DIR}/integration/ (suffix: .php)"
elif [ "$TESTSUITE" == "unit" ]; then
    echo "  - ${TEST_DIR}/unit/ (suffix: .php)"
elif [ "$TESTSUITE" == "wp-mock" ]; then
    echo "  - ${TEST_DIR}/wp-mock/ (suffix: .php)"
fi

# List test files if directory exists
if [ -d "${TEST_DIR}/${TESTSUITE}" ]; then
    echo "Found test files:"
    find "${TEST_DIR}/${TESTSUITE}" -name "*.php" -type f
else
    echo "Error: test directory not found at ${TEST_DIR}/${TESTSUITE}"
    exit 1
fi

# Show configuration
echo "Test configuration:"
echo "  Bootstrap: $BOOTSTRAP"
echo "  Test Suite: $TESTSUITE"
echo "  Directory: $DIRECTORY"
echo "  PHPUnit command: $CMD"
echo "  Current directory: $(pwd)"
# echo "  Test directory contents:"
# ls -la "${TEST_DIR}/"

echo "=== System Status Before Tests ==="
df -h
du -sh /tmp/* /var/tmp/* /app/* 2>/dev/null | sort -h

# Run tests
echo -e "\n${GREEN}Running:${NC} $CMD"
$CMD

echo "=== System Status After Tests ==="
df -h
du -sh /tmp/* /var/tmp/* /app/* 2>/dev/null | sort -h
