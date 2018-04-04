<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Client;

use BitWasp\Test\Trezor\Bridge\Message\TestCase;
use BitWasp\Test\Trezor\MockHttpStack;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Exception\InvalidMessageException;
use BitWasp\Trezor\Bridge\Exception\SchemaValidationException;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Bridge\Message\ListenResponse;
use GuzzleHttp\Psr7\Response;

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

        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode([$deviceRes]))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);

        $device = new Device($deviceReq);
        $response = $client->listen($device);
        $this->assertCount(1, $httpStack->getRequestLogs(), 'should perform all requests');
        $this->assertInstanceOf(ListenResponse::class, $response);
        $this->assertCount(1, $response->devices());
        $this->assertEquals($deviceRes, $response->devices()[0]->getObject());
    }

    public function testMockWithInvalidJson()
    {
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, ['Content-Type' => $this->contentTypeJson], "abcd1234")
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);

        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage("Invalid JSON received in response");

        $client->bridgeVersion();
    }

    public function testMockWithInvalidSchema()
    {
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode([]))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);

        $this->expectException(SchemaValidationException::class);

        $client->bridgeVersion();
    }
}
