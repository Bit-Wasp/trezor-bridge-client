<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Http;

use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Bridge\Message\Device;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class HttpClientTest extends TestCase
{
    public function testMockBridgeVersion()
    {
        $contentTypeJson = 'application/json';
        $body = [
            'version' => '1.0.0'
        ];

        $requests = [
            new Response(200, ['Content-Type' => $contentTypeJson], \json_encode($body)),
        ];

        // Create a mock and queue two responses.
        $mock = new MockHandler($requests);

        /** @var RequestInterface[] $container */
        $container = [];
        $history = Middleware::history($container);

        // Add the history middleware to the handler stack.
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $client = new \GuzzleHttp\Client([
            'handler' => $stack,
            'base_uri' => 'http://localhost:21325',
            'headers' => [
                'Origin' => 'http://localhost:5000',
            ],
        ]);

        $httpClient = new HttpClient($client);
        $response = $httpClient->bridgeVersion();

        $this->assertCount(count($requests), $container, 'should perform all requests');

        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertEquals("http://localhost:21325/", (string) $request->getUri());
        $this->assertCount(1, $request->getHeader('Accept'));
        $this->assertEquals($contentTypeJson, $request->getHeader('Accept')[0]);

        $decoded = \json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($body, $decoded, 'result should match');
    }

    public function testMockListDevices()
    {
        $contentTypeJson = 'application/json';
        $body = [
            [
                'path' => 'hid1234',
                'session' => '',
                'vendor' => '21324',
                'product' => '1',
            ]
        ];

        $requests = [
            new Response(200, ['Content-Type' => 'application/json'], \json_encode($body)),
        ];

        // Create a mock and queue two responses.
        $mock = new MockHandler($requests);

        /** @var RequestInterface[] $container */
        $container = [];
        $history = Middleware::history($container);

        // Add the history middleware to the handler stack.
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $client = new \GuzzleHttp\Client([
            'handler' => $stack,
            'base_uri' => 'http://localhost:21325',
            'headers' => [
                'Origin' => 'http://localhost:5000',
            ],
        ]);

        $httpClient = new HttpClient($client);
        $response = $httpClient->listDevices();

        $this->assertCount(count($requests), $container, 'should perform all requests');

        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertEquals("http://localhost:21325/enumerate", (string) $request->getUri());
        $this->assertCount(1, $request->getHeader('Accept'));
        $this->assertEquals($contentTypeJson, $request->getHeader('Accept')[0]);

        $decoded = \json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($body, $decoded, 'result should match');
    }

    public function getAcquireLastSessionId(): array
    {
        return [
            [null],
            ['123123123']
        ];
    }

    /**
     * @dataProvider getAcquireLastSessionId
     * @param string|null $someSessionId
     */
    public function testMockAcquire(string $someSessionId = null)
    {
        $contentTypeJson = 'application/json';
        $body = [
            'session' => '123123',
        ];

        $requests = [
            new Response(200, ['Content-Type' => 'application/json'], \json_encode($body)),
        ];

        // Create a mock and queue two responses.
        $mock = new MockHandler($requests);

        /** @var RequestInterface[] $container */
        $container = [];
        $history = Middleware::history($container);

        // Add the history middleware to the handler stack.
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $client = new \GuzzleHttp\Client([
            'handler' => $stack,
            'base_uri' => 'http://localhost:21325',
            'headers' => [
                'Origin' => 'http://localhost:5000',
            ],
        ]);

        $httpClient = new HttpClient($client);
        $device = new Device((object) [
            'path' => 'hid123',
            'session' => $someSessionId,
            'vendor' => '21324',
            'product' => '1',
        ]);
        $response = $httpClient->acquire($device);

        $this->assertCount(count($requests), $container, 'should perform all requests');

        /** @var RequestInterface $request */
        $request = $container[0]['request'];

        if ($someSessionId === null) {
            $this->assertEquals("http://localhost:21325/acquire/hid123/null", (string) $request->getUri());
        } else {
            $this->assertEquals("http://localhost:21325/acquire/hid123/{$someSessionId}", (string) $request->getUri());
        }

        $this->assertCount(1, $request->getHeader('Accept'));
        $this->assertEquals($contentTypeJson, $request->getHeader('Accept')[0]);

        $decoded = \json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($body, $decoded, 'result should match');
    }

    public function testMockRelease()
    {
        $contentTypeJson = 'application/json';
        $body = [];

        $requests = [
            new Response(200, ['Content-Type' => 'application/json'], \json_encode($body)),
        ];

        // Create a mock and queue two responses.
        $mock = new MockHandler($requests);

        /** @var RequestInterface[] $container */
        $container = [];
        $history = Middleware::history($container);

        // Add the history middleware to the handler stack.
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $client = new \GuzzleHttp\Client([
            'handler' => $stack,
            'base_uri' => 'http://localhost:21325',
            'headers' => [
                'Origin' => 'http://localhost:5000',
            ],
        ]);

        $someSessionId = '123123123';
        $httpClient = new HttpClient($client);
        $response = $httpClient->release($someSessionId);

        $this->assertCount(count($requests), $container, 'should perform all requests');

        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertEquals("http://localhost:21325/release/{$someSessionId}", (string) $request->getUri());
        $this->assertCount(1, $request->getHeader('Accept'));
        $this->assertEquals($contentTypeJson, $request->getHeader('Accept')[0]);

        $decoded = \json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($body, $decoded, 'result should match');
    }
}
