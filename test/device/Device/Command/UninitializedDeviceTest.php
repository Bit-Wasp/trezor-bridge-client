<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device;

use BitWasp\Test\Trezor\Device\Device\Command\CommandTest;
use BitWasp\Trezor\Device\Command\GetAddressService;
use BitWasp\Trezor\Device\Exception\Failure\NotInitializedException;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\Trezor\Device\UserInput\CurrentPinInput;
use BitWasp\Trezor\Device\UserInput\DisabledUserInputRequest;
use BitWasp\TrezorProto\InputScriptType;

class UninitializedDeviceTest extends CommandTest
{
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
