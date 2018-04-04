<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Client;

use BitWasp\Test\Trezor\Bridge\Message\TestCase;
use BitWasp\Test\Trezor\MockHttpStack;
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
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode([
                'version' => '1.0.0'
            ]))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $response = $client->bridgeVersion();

        $this->assertEquals('1.0.0', $response->version);
        $this->assertEquals('1.0.0', $response->version());
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

        try {
            $client->bridgeVersion();
            $this->fail("shouldn't succeed");
        } catch (SchemaValidationException $e) {
            $this->assertCount(1, $e->getErrors());
            $error = $e->getErrors()[0];
            $this->assertEquals("Array value found, but an object is required", $error['message']);
            throw $e;
        }
    }
}
