Trezor bridge client
====================

[![Build Status](https://travis-ci.org/Bit-Wasp/trezor-bridge-client.svg?branch=master)](https://travis-ci.org/Bit-Wasp/trezor-bridge-client)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Bit-Wasp/trezor-bridge-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Bit-Wasp/trezor-bridge-client/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Bit-Wasp/trezor-bridge-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Bit-Wasp/trezor-bridge-client/?branch=master)

This package exposes objects for interacting with the [trezord-go](https://github.com/trezor/trezord-go) package.

The trezord-go package runs a webserver and issues session ID's to software that needs to use a device.

It takes care of all the heavy lifting of speaking to a USB HID device, and exposes an API allowing us to pass protobuf messages to a trezor device.

# Installation

    composer require bit-wasp/trezor-bridge-client

# Contributing

See our [contributors guide](CONTRIBUTORS.md) for more information

# Testing

The library has two test cases: mock tests, where the HTTP layer is mocked and tested.
It also includes integration tests which run against a trezor emulator, and while running trezord-go.
