<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Client;

use BitWasp\Test\Trezor\Bridge\Message\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Exception\SchemaValidationException;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class ListDevicesTest extends TestCase
{
    private $contentTypeJson = 'application/json';

    public function testMockListDevices()
    {
        $body = [
            [
                'path' => 'hid1234',
                'session' => '',
                'vendor' => '21324',
                'product' => '1',
            ]
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
        $response = $client->listDevices();

        $this->assertCount(count($requests), $container, 'should perform all requests');

        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertEquals("http://localhost:21325/enumerate", (string) $request->getUri());
    }

    public function testMockWithInvalidSchema()
    {
        $requests = [
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode([
                'version' => '1.2.0'
            ])),
        ];

        // Create a mock and queue two responses.
        $mock = new MockHandler($requests);

        /** @var RequestInterface[] $container */
        $container = [];
        $history = Middleware::history($container);

        // Add the history middleware to the handler stack.
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $httpClient = HttpClient::forUri("http://localhost:21325", ['handler' => $stack,]);
        $client = new Client($httpClient);

        $this->expectException(SchemaValidationException::class);

        $client->listDevices();
    }
}
