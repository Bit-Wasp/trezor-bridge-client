<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Message;

class TestCase extends \BitWasp\Test\Trezor\TestCase
{
    public function createDevice(string $path, int $vendor = null, int $product = null, string $session = null)
    {
        $device = new \stdClass();
        $device->path = $path;
        $device->vendor = $vendor;
        $device->product = $product;
        $device->session = $session;
        return $device;
    }
}
