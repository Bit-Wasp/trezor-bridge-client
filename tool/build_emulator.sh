#!/bin/bash
cd $(git rev-parse --show-toplevel)/tool
export EMULATOR=1 TREZOR_TRANSPORT_V1=1
if [ ! -d ./emulator ]; then
    mkdir emulator
fi
if [ ! -d ./emulator/trezor-mcu ]; then
    cd emulator/
    git clone https://github.com/trezor/trezor-mcu
    cd trezor-mcu
    ./build-emulator.sh
fi
