<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Message;

use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Bridge\Message\ListDevicesResponse;

class ListDevicesResponseTest extends TestCase
{
    public function testListDevices()
    {
        $device1 = new Device($this->createDevice("hidab123123123", 21235, 1));
        $device2 = new Device($this->createDevice("hid429bd6c20a5df1", 21235, 1));
        $device3 = new Device($this->createDevice("hid429bd6c20a5df1", 21235, 1, "1"));

        $response = new ListDevicesResponse([$device1, $device2, $device3]);
        $this->assertCount(3, $response->devices());

        $this->assertSame($device1, $response->devices()[0]);
        $this->assertSame($device2, $response->devices()[1]);
        $this->assertSame($device3, $response->devices()[2]);
    }
}
