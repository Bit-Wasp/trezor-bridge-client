<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Device\Command;

use BitWasp\Test\Trezor\Device\Device\CommandTest;
use BitWasp\Trezor\Device\Command\GetAddressService;
use BitWasp\Trezor\Device\Command\InitializeService;
use BitWasp\Trezor\Device\Exception\Failure\NotInitializedException;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\Trezor\Device\UserInput\CurrentPinInput;
use BitWasp\Trezor\Device\UserInput\DisabledUserInputRequest;
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

    public function testUninitializedDevice()
    {
        $path = [44|0x80000000, 0|0x80000000, 0|0x80000000, 0, 0];

        $reqFactory = new RequestFactory();
        $getAddress = $reqFactory->getAddress('Bitcoin', $path, InputScriptType::SPENDADDRESS(), false);

        $getAddressService = new GetAddressService();
        $pinInput = new CurrentPinInput(new DisabledUserInputRequest());

        $this->expectException(NotInitializedException::class);

        $getAddressService->call($this->session, $pinInput, $getAddress);
    }
}
