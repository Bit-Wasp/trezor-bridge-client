<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Client;

use BitWasp\Test\Trezor\Bridge\Message\TestCase;
use BitWasp\Test\Trezor\MockHttpStack;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Exception\SchemaValidationException;
use BitWasp\Trezor\Bridge\Message\Device;
use GuzzleHttp\Psr7\Response;

class ReleaseTest extends TestCase
{
    private $contentTypeJson = 'application/json';

    public function testMockRelease()
    {
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode((object) []))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $res = $client->release('2');

        $this->assertCount(1, $httpStack->getRequestLogs(), 'should perform all requests');
        $this->assertTrue($res);
    }

    public function testMockInvalidSchema()
    {
        $deviceObj = $this->createDevice("hidabc123", 21324, 1);
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode([
                'session',
            ]))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($deviceObj);

        $this->expectException(SchemaValidationException::class);

        $client->acquire($device);
    }
}
