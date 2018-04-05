<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Service;

use BitWasp\Test\Trezor\MockHttpStack;
use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Codec\CallMessage\HexCodec;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Command\PingService;
use BitWasp\Trezor\Device\Exception\IncorrectNonceException;
use BitWasp\Trezor\Device\Exception\UnexpectedResultException;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\Trezor\Device\UserInput\CurrentPassphraseInput;
use BitWasp\Trezor\Device\UserInput\CurrentPinInput;
use BitWasp\Trezor\Device\UserInput\CommandLineUserInputRequest;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\Features;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PassphraseAck;
use BitWasp\TrezorProto\PassphraseRequest;
use BitWasp\TrezorProto\Ping;
use BitWasp\TrezorProto\PinMatrixAck;
use BitWasp\TrezorProto\PinMatrixRequest;
use BitWasp\TrezorProto\PinMatrixRequestType;
use BitWasp\TrezorProto\Success;
use GuzzleHttp\Psr7\Response;

class PingServiceTest extends TestCase
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
        $ping = $reqFactory->ping('abcd1234nonce', false, false, false);

        $pingService = new PingService();
        $pinInput = $this->getMockSinglePinInput('1234', 0);
        $pwInput = $this->getMockPwInput('securepassword', 0);

        $this->expectException(UnexpectedResultException::class);
        $this->expectExceptionMessage("Unexpected response, expecting Success, got BitWasp\\TrezorProto\\Features");

        $pingService->call($session, $ping, $pinInput, $pwInput);
    }

    public function testNoPromptButWrongNonce()
    {
        $success = new Success();
        $success->setMessage('someothernonceunrelated');

        $codec = new HexCodec();
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_Success()->value(), $success))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $ping = $reqFactory->ping('abcd1234nonce', false, false, false);

        $pingService = new PingService();
        $pinInput = $this->getMockSinglePinInput('1234', 0);
        $pwInput = $this->getMockPwInput('securepassword', 0);

        $this->expectException(IncorrectNonceException::class);

        $pingService->call($session, $ping, $pinInput, $pwInput);
    }

    public function testNoPrompt()
    {
        $success = new Success();
        $success->setMessage('abcd1234nonce');

        $codec = new HexCodec();
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_Success()->value(), $success))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $ping = $reqFactory->ping('abcd1234nonce', false, false, false);

        $pingService = new PingService();
        $pinInput = $this->getMockSinglePinInput('1234', 0);
        $pwInput = $this->getMockPwInput('securepassword', 0);

        $success = $pingService->call($session, $ping, $pinInput, $pwInput);

        $this->assertInstanceOf(Success::class, $success);
        $this->assertEquals('abcd1234nonce', $success->getMessage());
    }

    public function testRequireButton()
    {
        $buttonRequest = new ButtonRequest();
        $buttonRequest->setCode(ButtonRequestType::ButtonRequest_ProtectCall());

        $success = new Success();
        $success->setMessage('abcd1234nonce');

        $codec = new HexCodec();
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_ButtonRequest()->value(), $buttonRequest)),
            new Response(200, [], $codec->encode(MessageType::MessageType_Success()->value(), $success))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $ping = $reqFactory->ping('abcd1234nonce', true, false, false);

        $pingService = new PingService();
        $pinInput = $this->getMockSinglePinInput('1234', 0);
        $pwInput = $this->getMockPwInput('securepassword', 0);

        $success = $pingService->call($session, $ping, $pinInput, $pwInput);

        $this->assertInstanceOf(Success::class, $success);
        $this->assertEquals('abcd1234nonce', $success->getMessage());

        $this->assertCount(2, $httpStack->getRequestLogs());
        $req1 = $httpStack->getRequest(0);
        $req2 = $httpStack->getRequest(1);
        $this->assertEquals("http://localhost:21325/call/1", (string) $req1->getUri());
        $this->assertEquals("http://localhost:21325/call/1", (string) $req2->getUri());

        list ($req1Type, $req1Msg) = $codec->parsePayload($req1->getBody());
        $this->assertEquals(MessageType::MessageType_Ping()->value(), $req1Type);
        $ping1Dec = new Ping($req1Msg);
        $this->assertEquals('abcd1234nonce', $ping1Dec->getMessage());
        $this->assertTrue($ping1Dec->getButtonProtection());
        $this->assertFalse($ping1Dec->getPinProtection());
        $this->assertFalse($ping1Dec->getPassphraseProtection());

        list ($req2Type, $req2Msg) = $codec->parsePayload($req2->getBody());
        $this->assertEquals(MessageType::MessageType_ButtonAck()->value(), $req2Type);
        // not much to test in button ack
    }

    public function testRequirePin()
    {
        $pinRequest = new PinMatrixRequest();
        $pinRequest->setType(PinMatrixRequestType::PinMatrixRequestType_Current());

        $success = new Success();
        $success->setMessage('abcd1234nonce');

        $codec = new HexCodec();
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_PinMatrixRequest()->value(), $pinRequest)),
            new Response(200, [], $codec->encode(MessageType::MessageType_Success()->value(), $success))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $ping = $reqFactory->ping('abcd1234nonce', false, true, false);

        $pinInput = $this->getMockSinglePinInput('1234', 1);
        $pwInput = $this->getMockPwInput('securepassword', 0);

        $pingService = new PingService();
        $success = $pingService->call($session, $ping, $pinInput, $pwInput);

        $this->assertInstanceOf(Success::class, $success);
        $this->assertEquals('abcd1234nonce', $success->getMessage());

        $this->assertCount(2, $httpStack->getRequestLogs());
        $req1 = $httpStack->getRequest(0);
        $req2 = $httpStack->getRequest(1);
        $this->assertEquals("http://localhost:21325/call/1", (string) $req1->getUri());
        $this->assertEquals("http://localhost:21325/call/1", (string) $req2->getUri());

        list ($req1Type, $req1Msg) = $codec->parsePayload($req1->getBody());
        $this->assertEquals(MessageType::MessageType_Ping()->value(), $req1Type);
        $ping1Dec = new Ping($req1Msg);
        $this->assertEquals('abcd1234nonce', $ping1Dec->getMessage());
        $this->assertFalse($ping1Dec->getButtonProtection());
        $this->assertTrue($ping1Dec->getPinProtection());
        $this->assertFalse($ping1Dec->getPassphraseProtection());

        list ($req2Type, $req2Msg) = $codec->parsePayload($req2->getBody());
        $this->assertEquals(MessageType::MessageType_PinMatrixAck()->value(), $req2Type);
        $sentPinMatrix = new PinMatrixAck($req2Msg);
        $this->assertEquals('1234', $sentPinMatrix->getPin());
    }

    public function testRequirePassphrase()
    {
        $pinRequest = new PassphraseRequest();

        $success = new Success();
        $success->setMessage('abcd1234nonce');

        $codec = new HexCodec();
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_PassphraseRequest()->value(), $pinRequest)),
            new Response(200, [], $codec->encode(MessageType::MessageType_Success()->value(), $success))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $ping = $reqFactory->ping('abcd1234nonce', false, false, true);

        $pingService = new PingService();
        $pinInput = $this->getMockSinglePinInput('1234', 0);
        $pwInput = $this->getMockPwInput('thisisanawesomepassword', 1);
        $success = $pingService->call($session, $ping, $pinInput, $pwInput);

        $this->assertInstanceOf(Success::class, $success);
        $this->assertEquals('abcd1234nonce', $success->getMessage());

        $this->assertCount(2, $httpStack->getRequestLogs());
        $req1 = $httpStack->getRequest(0);
        $req2 = $httpStack->getRequest(1);
        $this->assertEquals("http://localhost:21325/call/1", (string) $req1->getUri());
        $this->assertEquals("http://localhost:21325/call/1", (string) $req2->getUri());

        list ($req1Type, $req1Msg) = $codec->parsePayload($req1->getBody());
        $this->assertEquals(MessageType::MessageType_Ping()->value(), $req1Type);
        $ping1Dec = new Ping($req1Msg);
        $this->assertEquals('abcd1234nonce', $ping1Dec->getMessage());
        $this->assertFalse($ping1Dec->getButtonProtection());
        $this->assertFalse($ping1Dec->getPinProtection());
        $this->assertTrue($ping1Dec->getPassphraseProtection());

        list ($req2Type, $req2Msg) = $codec->parsePayload($req2->getBody());
        $this->assertEquals(MessageType::MessageType_PassphraseAck()->value(), $req2Type);
        $sentPinMatrix = new PassphraseAck($req2Msg);
        $this->assertEquals('thisisanawesomepassword', $sentPinMatrix->getPassphrase());
    }
}
