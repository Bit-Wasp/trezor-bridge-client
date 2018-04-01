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
            new Response(200, ['Content-Type' => 'application/json'], '0011000002450a11626974636f696e7472657a6f722e636f6d100118062000321836423635333939463643414335463943424430383045343738014000520e74657374696e672d7472657a6f725a240a07426974636f696e120342544318002080897a2805489ee4a22450e4dba224580168005a270a07546573746e6574120454455354186f2080ade20428c40148cf8fd621509487d621580168005a240a0542636173681203424348180020a0c21e2805489ee4a22450e4dba2245800600068015a260a084e616d65636f696e12034e4d4318342080ade204280548e2c8f60c50feb9f60c580068005a260a084c697465636f696e12034c544318302080b48913283248e2c8f60c50feb9f60c580168005a280a08446f6765636f696e1204444f4745181e208094ebdc03281648fd95eb17509887eb17580068005a220a0444617368120444415348184c20a08d06281048cca5f91750f8a5f917580068005a240a055a6361736812035a454318b83920c0843d28bd39489ee4a22450e4dba224580068005a2b0a0c426974636f696e20476f6c641203425447182620a0c21e2817489ee4a22450e4dba2245801604f68015a250a0844696769427974651203444742181e20a0c21e2805489ee4a22450e4dba224580168005a270a084d6f6e61636f696e12044d4f4e41183220c096b1022837489ee4a22450e4dba2245801680060016a14723cf295a72ce07b96047901bb8c2e461a2488f872207651b7caba5aae0cc1c65c8304f760396f77606cd3990c991598f0e22a81e0077800800100880100980100a00100')
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
        $result = $httpClient->call($sessionId, new Message(MessageType::MessageType_Initialize(), new Initialize()));

        $this->assertCount(1, $container, 'should perform all requests');

        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertEquals("http://localhost:21325/call/{$sessionId}", (string) $request->getUri());
        $this->assertCount(0, $request->getHeader('Accept'));

        list ($requestType, $requestPayload) = $hexCodec->parsePayload($request->getBody());

        $this->assertEquals(MessageType::MessageType_Initialize()->value(), $requestType, 'request type should match');

        $this->assertEquals(MessageType::MessageType_Features()->value(), $result->getType(), 'result type should match');
        $this->assertInstanceOf(Features::class, $result->getProto(), 'result type should match');

        /** @var Features $features */
        $features = $result->getProto();

        $this->assertEquals("testing-trezor", $features->getLabel());
        $this->assertEquals("6B65399F6CAC5F9CBD080E47", $features->getDeviceId());
    }
}
