<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Message;

use BitWasp\Trezor\Bridge\Message\VersionResponse;

class VersionResponseTest extends TestCase
{
    public function testResponse()
    {
        $version = '1.2.1';
        $obj = new \stdClass();
        $obj->version = $version;
        $response = new VersionResponse($obj);
        $this->assertEquals($obj, $response->getObject());
        $this->assertEquals($version, $response->version());
        $this->assertEquals($version, $response->version);
    }
}
