<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge\Http;

use BitWasp\Trezor\Bridge\Codec\CallMessage;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\MessageType;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var array
     */
    private $jsonHeaders = [];

    /**
     * @var CallMessage\HexCodec
     */
    private $callCodec;

    public function __construct(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
        $this->jsonHeaders = [
            'Accept' => 'application/json',
        ];
        $this->callCodec = new CallMessage\HexCodec();
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

    public function call(string $sessionId, Message $message): Message
    {
        $result = $this->client->post("/call/{$sessionId}", [
            'body' => $this->callCodec->encode($message->getType(), $message->getProto()),
        ]);

        list ($type, $result) = $this->callCodec->parsePayload(
            $this->callCodec->convertHexPayloadToBinary($result->getBody())
        );

        $messageType = MessageType::valueOf($type)->name();
        $protoType = substr($messageType, strlen("MessageType_"));
        $reader = ["\\BitWasp\\TrezorProto\\{$protoType}", 'fromStream'];
        $protobuf = call_user_func($reader, $result);
        return new Message($type, $protobuf);
    }
}
