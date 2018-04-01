<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Http;

use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Bridge\Codec\CallMessage\HexCodec;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\Features;
use BitWasp\TrezorProto\Initialize;
use BitWasp\TrezorProto\MessageType;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class HttpClientTest extends TestCase
{
    public function getUris(): array
    {
        return [
            ['http://localhost:21325/'],
            ['http://127.0.0.1:20202/'],
        ];
    }

    /**
     * @dataProvider getUris
     * @param string $uri
     */
    public function testForUri(string $uri)
    {
        $requests = [
            new Response(200, ['Content-Type' => 'application/json'], \json_encode([
                'version' => '1.0.0'
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

        $client = HttpClient::forUri($uri, [
            'handler' => $stack,
        ]);
        $client->bridgeVersion();

        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertEquals($uri, (string) $request->getUri());
    }

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

    public function testMockListen()
    {
        $contentTypeJson = 'application/json';

        $deviceObj = new \stdClass();
        $deviceObj->path = "hidabc123";
        $deviceObj->session = null;
        $deviceObj->product = "21324";
        $deviceObj->vendor = "1";

        $deviceObj2 = clone $deviceObj;
        $deviceObj2->path = "hidabababababababa";

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], \json_encode([
                $deviceObj,
                $deviceObj2,
            ]))
        ]);

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

        $device1 = new Device($deviceObj);
        $device2 = new Device($deviceObj2);
        $httpClient = new HttpClient($client);
        $httpClient->listen($device1, $device2);

        $this->assertCount(1, $container, 'should perform all requests');

        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertEquals("http://localhost:21325/listen", (string) $request->getUri());
        $this->assertCount(1, $request->getHeader('Accept'));
        $this->assertEquals($contentTypeJson, $request->getHeader('Accept')[0]);

        $decodedBody = \json_decode($request->getBody()->getContents());
        $this->assertEquals([$deviceObj, $deviceObj2], $decodedBody, 'result should match');
    }

    public function testMockCall()
    {
        $deviceObj = new \stdClass();
        $deviceObj->path = "hidabc123";
        $deviceObj->session = null;
        $deviceObj->product = "21324";
        $deviceObj->vendor = "1";

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '001100000000' . bin2hex((new Features())->toStream()->getContents()))
        ]);

        /** @var RequestInterface[] $container */
        $container = [];

        // Add the history middleware to the handler stack.
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($container));

        $client = new \GuzzleHttp\Client([
            'handler' => $stack,
            'base_uri' => 'http://localhost:21325',
            'headers' => [
                'Origin' => 'http://localhost:5000',
            ],
        ]);

        $sessionId = '1';
        $hexCodec = new HexCodec();
        $httpClient = new HttpClient($client, $hexCodec);
        $httpClient->call($sessionId, new Message(MessageType::MessageType_Initialize(), new Initialize()));

        $this->assertCount(1, $container, 'should perform all requests');

        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertEquals("http://localhost:21325/call/{$sessionId}", (string) $request->getUri());
        $this->assertCount(0, $request->getHeader('Accept'));

        list ($type, $payload) = $hexCodec->parsePayload($hexCodec->convertHexPayloadToBinary($request->getBody()));
        //$this->assertEquals(, $decodedBody, 'result should match');
    }
}
