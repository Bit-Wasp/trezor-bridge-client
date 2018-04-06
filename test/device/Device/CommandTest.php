<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Device;

use BitWasp\Test\Trezor\Device\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Bridge\Session;

abstract class CommandTest extends TestCase
{
    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Session
     */
    protected $session;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->httpClient = HttpClient::forUri("http://localhost:21325");
        $this->client = new Client($this->httpClient);
        $devices = $this->client->listDevices()->devices();
        if (empty($devices)) {
            throw new \RuntimeException("No devices returned");
        }

        $this->session = $this->client->acquire($devices[0]);
    }

}
