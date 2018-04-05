<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Service;

use BitWasp\Test\Trezor\MockHttpStack;
use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Codec\CallMessage\HexCodec;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Command\VerifyMessageService;
use BitWasp\Trezor\Device\Exception\UnexpectedResultException;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\Features;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\Success;
use GuzzleHttp\Psr7\Response;

class VerifyMessageServiceTest extends TestCase
{
    public function testWrongResultType()
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
        $msg = "this is my message!";
        $addr = "1HksNAfGmaMYAAzidJcAdgfjXy89ajYWpD";
        $signature = base64_decode("HywU/GSkCe1fghjTt/D9YPA2pTXSZUfcT3WNn5XpnZGIcfQvuEZH2LGAXiBTsypIITmrwXF8LxZWq5MCLo/kxp0=");
        $verifyMsg = $reqFactory->verifyMessage('Bitcoin', $addr, $msg, $signature);

        $signMsgService = new VerifyMessageService();

        $this->expectException(UnexpectedResultException::class);
        $this->expectExceptionMessage("Unexpected response, expecting Success, got BitWasp\\TrezorProto\\Features");

        $signMsgService->call($session, $verifyMsg);
    }

    public function testSignMessage()
    {
        $btnReq = new ButtonRequest();
        $btnReq->setCode(ButtonRequestType::ButtonRequest_Other());

        $success = new Success();

        $codec = new HexCodec();
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_ButtonRequest()->value(), $btnReq)),
            new Response(200, [], $codec->encode(MessageType::MessageType_ButtonRequest()->value(), $btnReq)),
            new Response(200, [], $codec->encode(MessageType::MessageType_Success()->value(), $success))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');
        $reqFactory = new RequestFactory();
        $msg = "this is my message!";
        $addr = "1HksNAfGmaMYAAzidJcAdgfjXy89ajYWpD";
        $signature = base64_decode("HywU/GSkCe1fghjTt/D9YPA2pTXSZUfcT3WNn5XpnZGIcfQvuEZH2LGAXiBTsypIITmrwXF8LxZWq5MCLo/kxp0=");
        $verifyMsg = $reqFactory->verifyMessage('Bitcoin', $addr, $msg, $signature);

        $signMsgService = new VerifyMessageService();
        $signedMessage = $signMsgService->call($session, $verifyMsg);
        $this->assertCount(3, $httpStack->getRequestLogs());
        $this->assertInstanceOf(Success::class, $signedMessage);
    }
}
