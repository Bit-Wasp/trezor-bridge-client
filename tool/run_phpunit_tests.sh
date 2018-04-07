#!/bin/bash
set -x
cd $(git rev-parse --show-toplevel)
if [ "$PHPUNIT" = "true" ]; then make phpunit-ci-unit && echo "Tests OK"; fi
