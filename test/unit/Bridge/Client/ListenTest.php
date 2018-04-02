<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Client;

use BitWasp\Test\Trezor\Bridge\Message\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Exception\InvalidMessageException;
use BitWasp\Trezor\Bridge\Exception\SchemaValidationException;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Bridge\Message\Device;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class ListenTest extends TestCase
{
    private $contentTypeJson = 'application/json';

    public function testMockListen()
    {
        $deviceReq = new \stdClass();
        $deviceReq->path = "hidabc123";
        $deviceReq->session = null;
        $deviceReq->product = "21324";
        $deviceReq->vendor = "1";

        $deviceRes = clone ($deviceReq);
        $deviceRes->session = '2';

        $requests = [
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode([$deviceRes])),
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

        $device = new Device($deviceReq);
        $response = $client->listen($device);

        $this->assertCount(count($requests), $container, 'should perform all requests');

        $this->assertCount(1, $response->devices());
        $this->assertEquals($deviceRes, $response->devices()[0]->getObject());
    }

    public function testMockWithInvalidJson()
    {
        $requests = [
            new Response(200, ['Content-Type' => $this->contentTypeJson], "abcd1234"),
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

        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage("Invalid JSON received in response");

        $client->bridgeVersion();
    }

    public function testMockWithInvalidSchema()
    {
        $requests = [
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode([])),
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

        $this->expectException(SchemaValidationException::class);

        $client->bridgeVersion();
    }
}
