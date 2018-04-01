<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Message;

use BitWasp\Trezor\Bridge\Message\Device;

class DeviceTest extends TestCase
{

    public function getDeviceFixture(): array
    {
        return [
            ["hidabc123", 21324, 1, null, ],
            ["hida00a0a0a000a0a0a", null, null, "1", ],
        ];
    }

    /**
     * @dataProvider getDeviceFixture
     * @param string $path
     * @param int $vendor
     * @param int $product
     * @param string|null $session
     */
    public function testDevice(string $path, int $vendor = null, int $product = null, string $session = null)
    {
        $devObj = $this->createDevice($path, $vendor, $product, $session);
        $device = new Device($devObj);
        $this->assertEquals($path, $device->getPath());
        $this->assertEquals($path, $device->path);
        $this->assertEquals($vendor, $device->getVendor());
        $this->assertEquals($vendor, $device->vendor);
        $this->assertEquals($product, $device->getProduct());
        $this->assertEquals($product, $device->product);
        $this->assertEquals($session, $device->getSession());
        $this->assertEquals($session, $device->session);
    }
}
