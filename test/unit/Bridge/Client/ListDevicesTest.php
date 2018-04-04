<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Client;

use BitWasp\Test\Trezor\Bridge\Message\TestCase;
use BitWasp\Test\Trezor\MockHttpStack;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Exception\SchemaValidationException;
use GuzzleHttp\Psr7\Response;

class ListDevicesTest extends TestCase
{
    private $contentTypeJson = 'application/json';

    public function testMockListDevices()
    {
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode([[
                'path' => 'hid1234',
                'session' => '',
                'vendor' => '21324',
                'product' => '1',
            ]]))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $response = $client->listDevices();

        $this->assertCount(1, $response, 'should perform all requests');
        $this->assertCount(1, $response->devices(), 'should perform all requests');

        $device1 = $response->devices()[0];
        $this->assertEquals("hid1234", $device1->getPath());
        $this->assertEquals("21324", $device1->getVendor());
        $this->assertEquals("", $device1->getSession());
        $this->assertEquals("1", $device1->getProduct());
    }

    public function testMockWithInvalidSchema()
    {
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, ['Content-Type' => $this->contentTypeJson], \json_encode([
                'version' => '1.2.0'
            ]))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);

        $this->expectException(SchemaValidationException::class);

        $client->listDevices();
    }
}
