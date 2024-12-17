#!/bin/bash

# Help text
show_help() {
    echo "Usage: $0 [options]"
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
    echo ""
    echo "Advanced options (if needed):"
    echo "  --testsuite NAME    Run specific test suite"
    echo "  --group NAME        Run tests with specific group"
    echo "  --bootstrap FILE    Use specific bootstrap file"
    echo ""
    echo "Examples:"
    echo "  $0 --unit           # Run all unit tests"
    echo "  $0 --mock           # Run WP Mock tests"
    echo "  $0 --providers      # Run only provider tests"
    echo "  $0 --all           # Run all tests"
}

# Default values
PHPUNIT="vendor/bin/phpunit"
BOOTSTRAP=""
TESTSUITE=""
DIRECTORY=""
COVERAGE=""
GROUP=""

# Validate WP_ROOT is set
if [ -z "${WP_ROOT:-}" ]; then
    echo " Error: WP_ROOT environment variable must be set"
    echo "Usage examples:"
    echo "  Integration Tests (using WordPress test framework):"
    echo "  cd ~/sites/gl-color-palette-generator/ && \\"
    echo "  bash ./bin/sync-to-wp.sh && \\"
    echo "  cd ~/sites/wordpress/wp-content/plugins/gl-color-palette-generator && \\"
    echo "  WP_ROOT=/app bin/test.sh --integration"
    exit 1
fi

# Get absolute paths
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PLUGIN_DIR="$( cd "$SCRIPT_DIR/.." && pwd )"
TEST_DIR="$PLUGIN_DIR/tests"

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
    echo "Error: wordpress-develop not found at ${WP_ROOT}/wp-content/plugins/wordpress-develop"
    echo "Please run the test setup script:"
    echo "  cd ${WP_ROOT}"
    echo "  lando ssh -c \"cd wp-content/plugins/gl-color-palette-generator && bash ./bin/setup-plugin-tests.sh\""
    exit 1
fi

# Check for test files
if [ ! -f "${WP_TESTS_DIR}/includes/functions.php" ]; then
    echo "Error: WordPress test files not found at ${WP_TESTS_DIR}"
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
    echo "Error: wp-tests-config.php not found at ${WP_TESTS_DIR}/wp-tests-config.php"
    echo "Please run the test setup script:"
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
            BOOTSTRAP="${TEST_DIR}/bootstrap-wp-mock.php"
            TESTSUITE="unit"
            shift
            ;;
        --mock)
            BOOTSTRAP="${TEST_DIR}/bootstrap-wp-mock.php"
            TESTSUITE="unit"
            shift
            ;;
        --integration)
            BOOTSTRAP="${TEST_DIR}/bootstrap-wp.php"
            TESTSUITE="integration"
            shift
            ;;
        --providers)
            BOOTSTRAP="${TEST_DIR}/bootstrap-wp-mock.php"
            DIRECTORY="${TEST_DIR}/providers"
            shift
            ;;
        --api)
            BOOTSTRAP="${TEST_DIR}/bootstrap-wp-mock.php"
            DIRECTORY="${TEST_DIR}/api"
            shift
            ;;
        --admin)
            BOOTSTRAP="${TEST_DIR}/bootstrap-wp-mock.php"
            DIRECTORY="${TEST_DIR}/admin"
            shift
            ;;
        --core)
            BOOTSTRAP="${TEST_DIR}/bootstrap-wp-mock.php"
            DIRECTORY="${TEST_DIR}/core"
            shift
            ;;
        --all)
            # If --integration is also specified, use bootstrap-wp.php
            if [[ "$*" == *"--integration"* ]]; then
                BOOTSTRAP="${TEST_DIR}/bootstrap-wp.php"
            else
                BOOTSTRAP="${TEST_DIR}/bootstrap-wp-mock.php"
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
        *)
            echo "Unknown option: $1"
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
[[ -n "$DIRECTORY" ]] && CMD="$CMD $DIRECTORY"

# Add verbose output
CMD="$CMD --debug --verbose"

# Show test discovery info
echo "Looking for tests in:"
if [ "$TESTSUITE" == "integration" ]; then
    echo "  - ${TEST_DIR}/integration/"
    if [ ! -d "${TEST_DIR}/integration" ]; then
        echo "Error: integration test directory not found at ${TEST_DIR}/integration"
        exit 1
    fi
    # List test files
    echo "Found test files:"
    find "${TEST_DIR}/integration" -name "test-*.php" -type f

    # Show test file contents
    for test_file in $(find "${TEST_DIR}/integration" -name "test-*.php" -type f); do
        echo "Contents of $test_file:"
        cat "$test_file"
    done
elif [ "$TESTSUITE" == "unit" ]; then
    echo "  - ${TEST_DIR}/providers/"
    echo "  - ${TEST_DIR}/api/"
    echo "  - ${TEST_DIR}/admin/"
    echo "  - ${TEST_DIR}/core/"
fi

# Show configuration
echo "Test configuration:"
echo "  Bootstrap: $BOOTSTRAP"
echo "  Test Suite: $TESTSUITE"
echo "  Directory: $DIRECTORY"
echo "  PHPUnit command: $CMD"
echo "  Current directory: $(pwd)"
echo "  Test directory contents:"
ls -la "${TEST_DIR}/"

# Run tests
echo "Running: $CMD"
$CMD
