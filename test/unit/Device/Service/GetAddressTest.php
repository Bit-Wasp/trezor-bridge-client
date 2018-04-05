<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Service;

use BitWasp\Test\Trezor\MockHttpStack;
use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Codec\CallMessage\HexCodec;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Command\GetAddressService;
use BitWasp\Trezor\Device\Exception\UnexpectedResultException;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\Trezor\Device\UserInput\CurrentPinInput;
use BitWasp\Trezor\Device\UserInput\CommandLineUserInputRequest;
use BitWasp\TrezorProto\Address;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\Features;
use BitWasp\TrezorProto\InputScriptType;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PinMatrixRequest;
use BitWasp\TrezorProto\PinMatrixRequestType;
use GuzzleHttp\Psr7\Response;

class GetAddressTest extends TestCase
{
    public function testNoPrompt()
    {
        $path = [44|0x80000000, 0|0x80000000, 0|0x80000000, 0, 0];
        $address = new Address();
        $address->setAddress("1LvioYLh7u5mV6MfsgQ5L25rbWXZq2w8dX");

        $codec = new HexCodec();
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_Address()->value(), $address))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $getAddress = $reqFactory->getAddress('Bitcoin', $path, InputScriptType::SPENDADDRESS(), false);

        $getAddressService = new GetAddressService();
        $pinInput = new CurrentPinInput(new CommandLineUserInputRequest());
        $address = $getAddressService->call($session, $pinInput, $getAddress);

        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals("1LvioYLh7u5mV6MfsgQ5L25rbWXZq2w8dX", $address->getAddress());
    }

    public function testRequirePin()
    {
        $pinRequest = new PinMatrixRequest();
        $pinRequest->setType(PinMatrixRequestType::PinMatrixRequestType_Current());

        $path = [44|0x80000000, 0|0x80000000, 0|0x80000000, 0, 0];
        $address = new Address();
        $address->setAddress("1LvioYLh7u5mV6MfsgQ5L25rbWXZq2w8dX");

        $codec = new HexCodec();

        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_PinMatrixRequest()->value(), $pinRequest)),
            new Response(200, [], $codec->encode(MessageType::MessageType_Address()->value(), $address))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');
        $reqFactory = new RequestFactory();
        $getAddress = $reqFactory->getAddress('Bitcoin', $path, InputScriptType::SPENDADDRESS(), false);

        $pinInput = $this->getMockSinglePinInput('12345');

        $getAddressService = new GetAddressService();
        $address = $getAddressService->call($session, $pinInput, $getAddress);

        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals("1LvioYLh7u5mV6MfsgQ5L25rbWXZq2w8dX", $address->getAddress());
    }

    public function testRequirePinWithUser1ButtonRequest()
    {
        $pinRequest = new PinMatrixRequest();
        $pinRequest->setType(PinMatrixRequestType::PinMatrixRequestType_Current());

        $btnRequest = new ButtonRequest();
        $btnRequest->setCode(ButtonRequestType::ButtonRequest_Other());

        $path = [44|0x80000000, 0|0x80000000, 0|0x80000000, 0, 0];
        $address = new Address();
        $address->setAddress("1LvioYLh7u5mV6MfsgQ5L25rbWXZq2w8dX");

        $codec = new HexCodec();

        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_PinMatrixRequest()->value(), $btnRequest)),
            new Response(200, [], $codec->encode(MessageType::MessageType_ButtonRequest()->value(), $btnRequest)),
            new Response(200, [], $codec->encode(MessageType::MessageType_Address()->value(), $address))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');
        $reqFactory = new RequestFactory();
        $getAddress = $reqFactory->getAddress('Bitcoin', $path, InputScriptType::SPENDADDRESS(), true);

        $pinInput = $this->getMockSinglePinInput('12345');

        $getAddressService = new GetAddressService();
        $address = $getAddressService->call($session, $pinInput, $getAddress);

        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals("1LvioYLh7u5mV6MfsgQ5L25rbWXZq2w8dX", $address->getAddress());
    }

    public function testRequirePinWithUser2ButtonRequests()
    {
        $pinRequest = new PinMatrixRequest();
        $pinRequest->setType(PinMatrixRequestType::PinMatrixRequestType_Current());

        $btnRequest = new ButtonRequest();
        $btnRequest->setCode(ButtonRequestType::ButtonRequest_Other());

        $path = [44|0x80000000, 0|0x80000000, 0|0x80000000, 0, 0];
        $address = new Address();
        $address->setAddress("1LvioYLh7u5mV6MfsgQ5L25rbWXZq2w8dX");

        $codec = new HexCodec();

        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_PinMatrixRequest()->value(), $btnRequest)),
            new Response(200, [], $codec->encode(MessageType::MessageType_ButtonRequest()->value(), $btnRequest)),
            new Response(200, [], $codec->encode(MessageType::MessageType_ButtonRequest()->value(), $btnRequest)),
            new Response(200, [], $codec->encode(MessageType::MessageType_Address()->value(), $address))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');
        $reqFactory = new RequestFactory();
        $getAddress = $reqFactory->getAddress('Bitcoin', $path, InputScriptType::SPENDADDRESS(), true);

        $pinInput = $this->getMockSinglePinInput('12345');

        $getAddressService = new GetAddressService();
        $address = $getAddressService->call($session, $pinInput, $getAddress);

        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals("1LvioYLh7u5mV6MfsgQ5L25rbWXZq2w8dX", $address->getAddress());
    }

    public function testReturnsUnexpectedType()
    {
        $features = new Features();
        $codec = new HexCodec();
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_Features()->value(), $features))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $path = [44|0x80000000, 0|0x80000000, 0|0x80000000, 0, 0];
        $getAddress = $reqFactory->getAddress('Bitcoin', $path, InputScriptType::SPENDADDRESS(), false);

        $pinInput = new CurrentPinInput(new CommandLineUserInputRequest());
        $getPublicKeyService = new GetAddressService();

        $this->expectException(UnexpectedResultException::class);
        $this->expectExceptionMessage("Unexpected response, expecting Address, got BitWasp\\TrezorProto\\Features");

        $getPublicKeyService->call($session, $pinInput, $getAddress);
    }
}
