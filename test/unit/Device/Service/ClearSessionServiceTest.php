<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Service;

use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Codec\CallMessage\HexCodec;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Command\ClearSessionService;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\TrezorProto\Features;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\Success;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class ClearSessionServiceTest extends TestCase
{
    public function testWrongResultType()
    {
        $wrongMsg = new Features();

        $codec = new HexCodec();
        $requests = [
            new Response(200, [], $codec->encode(MessageType::MessageType_Features()->value(), $wrongMsg)),
        ];

        // Create a mock and queue two responses.
        $mock = new MockHandler($requests);

        /** @var RequestInterface[] $container */
        $container = [];
        $history = Middleware::history($container);

        // Add the history middleware to the handler stack.
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $httpClient = HttpClient::forUri("http://localhost:21325/", ['handler' => $stack,]);
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $clearSession = $reqFactory->clearSession();

        $clrSessionService = new ClearSessionService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Unexpected response, expecting Success, got BitWasp\\TrezorProto\\Features");

        $clrSessionService->call($session, $clearSession);
    }

    public function testReturnsSuccess()
    {
        $features = new Success();

        $codec = new HexCodec();
        $requests = [
            new Response(200, [], $codec->encode(MessageType::MessageType_Success()->value(), $features)),
        ];

        // Create a mock and queue two responses.
        $mock = new MockHandler($requests);

        /** @var RequestInterface[] $container */
        $container = [];
        $history = Middleware::history($container);

        // Add the history middleware to the handler stack.
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $httpClient = HttpClient::forUri("http://localhost:21325/", ['handler' => $stack,]);
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $clearSession = $reqFactory->clearSession();

        $clrSessionService = new ClearSessionService();
        $features = $clrSessionService->call($session, $clearSession);

        $this->assertInstanceOf(Success::class, $features);
    }
}
