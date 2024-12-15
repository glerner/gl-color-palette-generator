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

# Parse arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --help)
            show_help
            exit 0
            ;;
        --unit)
            BOOTSTRAP="tests/bootstrap-wp-mock.php"
            DIRECTORY="tests/providers tests/api tests/admin"
            shift
            ;;
        --mock)
            BOOTSTRAP="tests/bootstrap-wp-mock.php"
            TESTSUITE="unit"
            shift
            ;;
        --integration)
            BOOTSTRAP="tests/bootstrap-wp.php"
            TESTSUITE="integration"
            shift
            ;;
        --providers)
            BOOTSTRAP="tests/bootstrap-wp-mock.php"
            DIRECTORY="tests/providers"
            shift
            ;;
        --api)
            BOOTSTRAP="tests/bootstrap-wp-mock.php"
            DIRECTORY="tests/api"
            shift
            ;;
        --admin)
            BOOTSTRAP="tests/bootstrap-wp-mock.php"
            DIRECTORY="tests/admin"
            shift
            ;;
        --core)
            BOOTSTRAP="tests/bootstrap-wp-mock.php"
            DIRECTORY="tests/core"
            shift
            ;;
        --all)
            BOOTSTRAP="tests/bootstrap-wp-mock.php"
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

# Run tests
echo "Running: $CMD"
$CMD
