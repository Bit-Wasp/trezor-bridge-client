#!/bin/bash
set -x
cd $(git rev-parse --show-toplevel)/tool
if [ ! -d $GOPATH/src/github.com/trezor/trezord-go ]; then
    mkdir -p $GOPATH/src/github.com/trezor/trezord-go
    cd $GOPATH/src/github.com/trezor/trezord-go
    git init
    #git remote add bit-wasp https://github.com/bit-wasp/trezord-go
    #git fetch bit-wasp nousb
    #git checkout bit-wasp/nousb

    git remote add origin https://github.com/trezor/trezord-go
    git fetch --all --tags --prune
    git checkout $TREZOR_BRIDGE_VERSION
    go build
    cp trezord-go $GOPATH/bin/trezord-go
fi
