<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Service;

use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Codec\CallMessage\HexCodec;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Command\InitializeService;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\TrezorProto\Features;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\Success;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class InitializeServiceTest extends TestCase
{
    public function testNoPromptButWrongResultType()
    {
        $wrongMsg = new Success();

        $codec = new HexCodec();
        $requests = [
            new Response(200, [], $codec->encode(MessageType::MessageType_Success()->value(), $wrongMsg)),
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
        $initialize = $reqFactory->initialize();

        $initService = new InitializeService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Unexpected response, expecting Features, got BitWasp\\TrezorProto\\Success");

        $initService->call($session, $initialize);
    }

    public function testReturnsFeatures()
    {
        $features = new Features();

        $codec = new HexCodec();
        $requests = [
            new Response(200, [], $codec->encode(MessageType::MessageType_Features()->value(), $features)),
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
        $initialize = $reqFactory->initialize();

        $initService = new InitializeService();
        $features = $initService->call($session, $initialize);

        $this->assertInstanceOf(Features::class, $features);
    }
}
