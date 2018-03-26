#!/bin/bash
set -x
cd $(git rev-parse --show-toplevel)
if [ "$CODE_STYLE" = "true" ]; then make phpcs && echo "Code style OK"; fi
if [ "$CODE_STYLE" = "true" ]; then vendor/bin/phpstan analyze -l 7 src test && echo "Static analysis OK"; fi
if [ "$CODE_STYLE" = "true" ]; then vendor/bin/infection && echo "Mutation analysis OK"; fi
