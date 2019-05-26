<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Device\Command;

use BitWasp\Trezor\Device\Button\DebugButtonAck;
use BitWasp\Trezor\Device\Command\InitializeService;
use BitWasp\Trezor\Device\Command\LoadDeviceService;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\TrezorProto\Initialize;
use BitWasp\TrezorProto\Success;

class LoadDeviceServiceTest extends CommandTest
{
    /*public function testLoadWithMnemonic()
    {
        $mnemonic = "memory top document curren spike embrace squeeze acid judge shop body organ coyote sustain abandon maid obtain bridge ankle across forest chuckle prepare yellow";
        $language = "EN";

        $reqFactory = new RequestFactory();
        $getAddress = $reqFactory->loadDeviceWithMnemonic($mnemonic, $language);

        $debugSession = $this->client->acquire($this->devices[1]);
        $buttonAck = new DebugButtonAck($debugSession, true);
        $loadDeviceService = new LoadDeviceService($buttonAck);

        $success = $loadDeviceService->call($this->session, $getAddress);
    }*/

    public function testLoadWithHdNode()
    {
        $depth = 0;
        $fingerprint = 0;
        $numChild = 0;
        $chainCode = \Protobuf\Stream::fromString(hex2bin('a86d0945bd342199a130b65255df75199fe09e539d60053003cc1c0e999982a5'));
        $privateKey = \Protobuf\Stream::fromString(hex2bin('874c62f2c98f7c94f1a691492825a71e8e9b9251f03c208f37d1ec9c9cda2b24'));
        $language = "EN";

        $reqFactory = new RequestFactory();
        $hdNode = $reqFactory->privateHdNode($depth, $fingerprint, $numChild, $chainCode, $privateKey);
        $loadDevice = $reqFactory->loadDeviceWithHdNode($hdNode, $language);

        $debugSession = $this->client->acquire($this->devices[1]);
        $buttonAck = new DebugButtonAck($debugSession, true);
        $loadDeviceService = new LoadDeviceService($buttonAck);
        $initializeService = new InitializeService();

        $features = $initializeService->call($this->session, new Initialize());
        $this->assertFalse($features->getInitialized());
        $success = $loadDeviceService->call($this->session, $loadDevice);
        $this->assertInstanceOf(Success::class, $success);
    }
}
