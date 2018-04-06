#!/bin/bash
set -x
cd $(git rev-parse --show-toplevel)/tool
if [ ! -d ./bridge ]; then
    mkdir bridge
fi
if [ ! -d $GOPATH/src/github.com/trezor/trezord-go ]; then
    mkdir -p $GOPATH/src/github.com/trezor/trezord-go
    cd $GOPATH/src/github.com/trezor/trezord-go
    git init
    git remote add bit-wasp https://github.com/bit-wasp/trezord-go
    git fetch bit-wasp nousb
    git checkout bit-wasp/nousb
    go install .
fi
