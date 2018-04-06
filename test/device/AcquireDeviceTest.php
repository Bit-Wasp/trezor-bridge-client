<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device;

use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Device\Command\PingService;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\Trezor\Device\UserInput\CurrentPassphraseInput;
use BitWasp\Trezor\Device\UserInput\CurrentPinInput;
use BitWasp\Trezor\Device\UserInput\DisabledUserInputRequest;

class AcquireDeviceTest extends TestCase
{
    public function testBridge()
    {
        $httpClient = HttpClient::forUri("http://localhost:21325");
        $client = new Client($httpClient);
        $devices = $client->listDevices();
        $this->assertCount(1, $devices);
        $this->assertEquals("emulator21324", $devices->devices()[0]->getPath());
        $this->assertEquals(null, $devices->devices()[0]->getSession());

        $session = $client->acquire($devices->devices()[0]);

        $ping = new PingService();
        $factory = new RequestFactory();
        $pinInput = new CurrentPinInput(new DisabledUserInputRequest());
        $pwInput = new CurrentPassphraseInput(new DisabledUserInputRequest());
        $result = $ping->call($session, $factory->ping('abc', false, false, false), $pinInput, $pwInput);
        $this->assertEquals('abc', $result->getMessage());

        $devices = $client->listDevices();
        $this->assertCount(1, $devices);
        $this->assertEquals("emulator21324", $devices->devices()[0]->getPath());
        $this->assertEquals($session->getSessionId(), $devices->devices()[0]->getSession());

        $session->release();

        $devices = $client->listDevices();
        $this->assertCount(1, $devices);
        $this->assertEquals("emulator21324", $devices->devices()[0]->getPath());
        $this->assertEquals(null, $devices->devices()[0]->getSession());
    }
}
