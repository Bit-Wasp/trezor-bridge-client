#!/bin/bash
cd $(git rev-parse --show-toplevel)
if [ "${INTEGRATION}" = "true" ]; then
    tool/build_bridge.sh
    tool/build_emulator.sh
    make phpunit-ci-integration
fi
