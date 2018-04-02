<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Client;

use BitWasp\Test\Trezor\Bridge\Message\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Exception\SchemaValidationException;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Bridge\Message\Device;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class ReleaseTest extends TestCase
{
    private $contentTypeJson = 'application/json';

    public function testMockRelease()
    {
        $requests = [
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode((object) [])),
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

        $res = $client->release('2');

        $this->assertCount(count($requests), $container, 'should perform all requests');

        $this->assertTrue($res);
    }

    public function testMockInvalidSchema()
    {
        $deviceObj = $this->createDevice("hidabc123", 21324, 1);

        $requests = [
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode([
                'session',
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

        $httpClient = HttpClient::forUri("http://localhost:21325/", ['handler' => $stack,]);
        $client = new Client($httpClient);

        $device = new Device($deviceObj);

        $this->expectException(SchemaValidationException::class);

        $client->acquire($device);
    }
}
