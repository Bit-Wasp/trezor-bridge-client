<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Client;

use BitWasp\Test\Trezor\Bridge\Message\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Exception\InvalidMessageException;
use BitWasp\Trezor\Bridge\Exception\SchemaValidationException;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class BridgeVersionTest extends TestCase
{
    private $contentTypeJson = 'application/json';

    public function testMockBridgeVersion()
    {
        $body = [
            'version' => '1.0.0'
        ];

        $requests = [
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode($body)),
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
        $response = $client->bridgeVersion();

        $this->assertCount(count($requests), $container, 'should perform all requests');

        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertEquals("http://localhost:21325/", (string) $request->getUri());
        $this->assertCount(1, $request->getHeader('Accept'));
        $this->assertEquals($this->contentTypeJson, $request->getHeader('Accept')[0]);
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
