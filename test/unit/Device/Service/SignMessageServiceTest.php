<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Service;

use BitWasp\Test\Trezor\MockHttpStack;
use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Codec\CallMessage\HexCodec;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Command\SignMessageService;
use BitWasp\Trezor\Device\Exception\UnexpectedResultException;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\Trezor\Device\UserInput\CurrentPinInput;
use BitWasp\Trezor\Device\UserInput\FgetsUserInputRequest;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\Features;
use BitWasp\TrezorProto\MessageSignature;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PinMatrixRequest;
use BitWasp\TrezorProto\PinMatrixRequestType;
use GuzzleHttp\Psr7\Response;

class SignMessageServiceTest extends TestCase
{
    public function testNoPromptButWrongResultType()
    {
        $wrongMsg = new Features();

        $codec = new HexCodec();
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_Features()->value(), $wrongMsg))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $msg = "some message";
        $signMsg = $reqFactory->signMessagePubKeyHash('Bitcoin', [44|0x80000000, 0|0x80000000, 0|0x80000000, 0, 0], $msg);

        $currentPin = new CurrentPinInput(new FgetsUserInputRequest());
        $signMsgService = new SignMessageService();

        $this->expectException(UnexpectedResultException::class);
        $this->expectExceptionMessage("Unexpected response, expecting MessageSignature, got BitWasp\\TrezorProto\\Features");

        $signMsgService->call($session, $currentPin, $signMsg);
    }

    public function testSignMessage()
    {
        $btnReq = new ButtonRequest();
        $btnReq->setCode(ButtonRequestType::ButtonRequest_ProtectCall());

        $pinMatrixReq = new PinMatrixRequest();
        $pinMatrixReq->setType(PinMatrixRequestType::PinMatrixRequestType_Current());

        $sig = \Protobuf\Stream::fromString(base64_decode("HywU/GSkCe1fghjTt/D9YPA2pTXSZUfcT3WNn5XpnZGIcfQvuEZH2LGAXiBTsypIITmrwXF8LxZWq5MCLo/kxp0="));
        $signedMsg = new MessageSignature();
        $signedMsg->setSignature($sig);
        $signedMsg->setAddress("1HksNAfGmaMYAAzidJcAdgfjXy89ajYWpD");

        $codec = new HexCodec();
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_ButtonRequest()->value(), $btnReq)),
            new Response(200, [], $codec->encode(MessageType::MessageType_PinMatrixRequest()->value(), $pinMatrixReq)),
            new Response(200, [], $codec->encode(MessageType::MessageType_MessageSignature()->value(), $signedMsg))
        );
        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');
        $reqFactory = new RequestFactory();
        $msg = "some message";
        $signMsg = $reqFactory->signMessagePubKeyHash('Bitcoin', [44|0x80000000, 0|0x80000000, 0|0x80000000, 0, 0], $msg);

        $currentPin = $this->getMockSinglePinInput('123456');

        $signMsgService = new SignMessageService();
        $signedMessage = $signMsgService->call($session, $currentPin, $signMsg);
        $this->assertCount(3, $httpStack->getRequestLogs());
        $this->assertInstanceOf(MessageSignature::class, $signedMessage);
        $this->assertEquals(
            base64_decode("HywU/GSkCe1fghjTt/D9YPA2pTXSZUfcT3WNn5XpnZGIcfQvuEZH2LGAXiBTsypIITmrwXF8LxZWq5MCLo/kxp0="),
            $signedMessage->getSignature()->getContents()
        );
        $this->assertEquals("1HksNAfGmaMYAAzidJcAdgfjXy89ajYWpD", $signedMessage->getAddress());
    }
}
