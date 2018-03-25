<?php

declare(strict_types=1);

namespace BitWasp\Trezor;

use BitWasp\Trezor\Message\Device;
use Protobuf\Message;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    private $client;
    private $jsonHeaders = [];
    public function __construct(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
        $this->jsonHeaders = [
            'Accept' => 'application/json',
        ];
    }

    public static function forUri(string $uri)
    {
        return new self(new \GuzzleHttp\Client([
            'base_uri' => $uri,
            'headers' => [
                'Origin' => 'http://localhost:5000',
            ],
        ]));
    }

    public function bridgeVersion(): ResponseInterface
    {
        return $this->client->post('/', [
            'headers' => $this->jsonHeaders,
        ]);
    }

    public function listDevices(): ResponseInterface
    {
        return $this->client->post('/enumerate', [
            'headers' => $this->jsonHeaders,
        ]);
    }

    public function listen(Device ...$devices): ResponseInterface
    {
        return $this->client->post('/listen', [
            'headers' => $this->jsonHeaders,
            'json' => array_map(function (Device $device): \stdClass {
                return $device->getObject();
            }, $devices),
        ]);
    }

    public function acquire(Device $device): ResponseInterface
    {
        $prevSession = $device->getSession() ?: "null";
        return $this->client->post("/acquire/{$device->getPath()}/{$prevSession}", [
            'headers' => $this->jsonHeaders,
        ]);
    }

    public function release(string $sessionId): ResponseInterface
    {
        return $this->client->post("/release/{$sessionId}", [
            'headers' => $this->jsonHeaders,
        ]);
    }

    public function call(string $sessionId, int $messageType, Message $message): ResponseInterface
    {
        $stream = $message->toStream();
        $payload = unpack('H*', pack('n', $messageType) . pack('N', $stream->getSize()) . $stream->getContents())[1];
        return $this->client->post("/call/{$sessionId}", [
            'body' => $payload,
        ]);
    }
}
