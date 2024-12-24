#!/bin/bash

# Run PHPUnit with output redirected to stderr
vendor/bin/phpunit \
  --bootstrap=tests/bootstrap-wp.php \
  --testsuite integration \
  --debug \
  --verbose \
  --stderr \
  --no-progress \
  "$@"
