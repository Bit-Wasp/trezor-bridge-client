<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Service;

use BitWasp\Test\Trezor\MockHttpStack;
use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Codec\CallMessage\HexCodec;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Command\GetEntropyService;
use BitWasp\Trezor\Device\Exception\UnexpectedResultException;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\Entropy;
use BitWasp\TrezorProto\Features;
use BitWasp\TrezorProto\MessageType;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class GetEntropyServiceTest extends TestCase
{
    public function testButtonAck()
    {
        $buttonRequest = new ButtonRequest();
        $buttonRequest->setCode(ButtonRequestType::ButtonRequest_ProtectCall());

        $retData = '42424242424242424242424242424242';
        $entropy = new Entropy();
        $entropy->setEntropy(\Protobuf\Stream::fromString($retData));

        $codec = new HexCodec();
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_ButtonRequest()->value(), $buttonRequest)),
            new Response(200, [], $codec->encode(MessageType::MessageType_Entropy()->value(), $entropy))
        );

        $httpClient = $httpStack->getClient();

        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $getEntropy = $reqFactory->getEntropy(32);

        $getEntropyService = new GetEntropyService();
        $entropy = $getEntropyService->call($session, $getEntropy);

        $this->assertInstanceOf(Entropy::class, $entropy);
        $this->assertEquals($retData, $entropy->getEntropy()->getContents());
    }

    public function testReturnsWrongType()
    {
        $features = new Features();

        $codec = new HexCodec();
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_Features()->value(), $features))
        );

        $httpClient = $httpStack->getClient();

        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $getEntropy = $reqFactory->getEntropy(32);

        $getEntropyService = new GetEntropyService();

        $this->expectException(UnexpectedResultException::class);
        $this->expectExceptionMessage("Unexpected message returned, expecting Entropy");

        $getEntropyService->call($session, $getEntropy);
    }
}
