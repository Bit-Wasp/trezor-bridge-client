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
use BitWasp\Trezor\Device\Command\GetPublicKeyService;
use BitWasp\Trezor\Device\Exception\UnexpectedResultException;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\InputScriptType;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PinMatrixRequest;
use BitWasp\TrezorProto\PinMatrixRequestType;
use GuzzleHttp\Psr7\Response;

class AbstractDeviceServiceTest extends TestCase
{
    public function testRequireExactlyCurrentPin()
    {
        $pinRequest = new PinMatrixRequest();
        $pinRequest->setType(PinMatrixRequestType::PinMatrixRequestType_NewFirst());

        $codec = new HexCodec();

        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_PinMatrixRequest()->value(), $pinRequest))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $path = [44|0x80000000, 0|0x80000000, 0|0x80000000, 0, 0];
        $getPublicKey = $reqFactory->getPublicKey('Bitcoin', $path, false);

        $pinInput = $this->getMockSinglePinInput('12345', 0);

        $getPublicKeyService = new GetPublicKeyService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Unexpected pin matrix type (was PinMatrixRequestType_NewFirst, not expected type PinMatrixRequestType_Current)");

        $getPublicKeyService->call($session, $pinInput, $getPublicKey);
    }

    public function testRequireExactlyButtonType()
    {
        $btnReq = new ButtonRequest();
        $btnReq->setCode(ButtonRequestType::ButtonRequest_ConfirmOutput());

        $codec = new HexCodec();

        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_ButtonRequest()->value(), $btnReq))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $path = [44|0x80000000, 0|0x80000000, 0|0x80000000, 0, 0];
        $getAddress = $reqFactory->getAddress('Bitcoin', $path, InputScriptType::SPENDADDRESS(), true);

        $pinInput = $this->getMockSinglePinInput('12345', 0);

        $getAddrService = new GetAddressService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Unexpected button request (expected: ButtonRequest_Other, got ButtonRequest_ConfirmOutput)");

        $getAddrService->call($session, $pinInput, $getAddress);
    }
}
