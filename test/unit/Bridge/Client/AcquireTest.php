<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Client;

use BitWasp\Test\Trezor\Bridge\Message\TestCase;
use BitWasp\Test\Trezor\MockHttpStack;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Exception\SchemaValidationException;
use BitWasp\Trezor\Bridge\Message\Device;
use GuzzleHttp\Psr7\Response;

class AcquireTest extends TestCase
{
    private $contentTypeJson = 'application/json';

    public function testMockListen()
    {
        $deviceObj = $this->createDevice("hidabc123", 21324, 1);

        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode([
                'session' => '2',
            ]))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);

        $device = new Device($deviceObj);
        $session = $client->acquire($device);

        $this->assertCount(1, $httpStack->getRequestLogs(), 'should perform all requests');

        $this->assertEquals('2', $session->getSessionId());
        $this->assertSame($device, $session->getDevice());
    }

    public function testMockWithInvalidSchema()
    {
        $deviceObj = $this->createDevice("hidabc123", 21324, 1);

        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode([
                'session'
            ]))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($deviceObj);

        $this->expectException(SchemaValidationException::class);

        try {
            $client->acquire($device);
        } catch (SchemaValidationException $e) {
            $this->assertCount(1, $e->getErrors());
            throw $e;
        }
    }
}
