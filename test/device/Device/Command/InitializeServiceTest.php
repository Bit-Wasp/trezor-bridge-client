<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Device\Command;

use BitWasp\Trezor\Device\Command\InitializeService;
use BitWasp\TrezorProto\Features;
use BitWasp\TrezorProto\Initialize;

class InitializeServiceTest extends CommandTest
{
    public function testInitialize()
    {
        $initialize = new Initialize();
        $service = new InitializeService();
        $features = $service->call($this->session, $initialize);
        $this->assertInstanceOf(Features::class, $features);
        $this->assertEquals("bitcointrezor.com", $features->getVendor());
        $this->assertFalse($features->getInitialized());
    }
}
