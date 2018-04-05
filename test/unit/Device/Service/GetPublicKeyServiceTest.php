<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Service;

use BitWasp\Test\Trezor\MockHttpStack;
use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Codec\CallMessage\HexCodec;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Command\GetPublicKeyService;
use BitWasp\Trezor\Device\Exception\UnexpectedResultException;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\Trezor\Device\UserInput\CurrentPinInput;
use BitWasp\Trezor\Device\UserInput\CommandLineUserInputRequest;
use BitWasp\TrezorProto\Features;
use BitWasp\TrezorProto\HDNodeType;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PinMatrixRequest;
use BitWasp\TrezorProto\PinMatrixRequestType;
use BitWasp\TrezorProto\PublicKey;
use GuzzleHttp\Psr7\Response;

class GetPublicKeyServiceTest extends TestCase
{
    public function testNoPrompt()
    {
        $path = [44|0x80000000, 0|0x80000000, 0|0x80000000, 0, 0];
        $hdNode = new HDNodeType();
        $hdNode->setDepth(5);
        $hdNode->setChildNum(0);
        $hdNode->setFingerprint(3980090076);
        $hdNode->setChainCode(\Protobuf\Stream::fromString(hex2bin("36770a996cb1995a8ec87be4a45d2513a1ac43cbe96bfb950ce4a121f3354cee")));
        $hdNode->setPublicKey(\Protobuf\Stream::fromString(hex2bin("0284538fa544ca7070f3548959819669312ee462aed6eb04ba443a97183376cbfe")));

        $publicKey = new PublicKey();
        $publicKey->setNode($hdNode);
        $publicKey->setXpub("xprvA4Apf1Dg1po8BsqxwFb22gfDpJzoTrRDTTbspdoZjZE3BkYfWoWphDaxQXcAji7ciLagegZ2q8kU2yjfE9gs6EEDsbhZMMT1t3ivCGupCqk");

        $codec = new HexCodec();
        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_PublicKey()->value(), $publicKey))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $getPublicKey = $reqFactory->getPublicKey('Bitcoin', $path, false);

        $pinInput = new CurrentPinInput(new CommandLineUserInputRequest());
        $getPublicKeyService = new GetPublicKeyService();
        $success = $getPublicKeyService->call($session, $pinInput, $getPublicKey);

        $this->assertInstanceOf(PublicKey::class, $success);
        $this->assertEquals($publicKey->getXpub(), $success->getXpub());
        $this->assertEquals($hdNode->getDepth(), $success->getNode()->getDepth());
        $this->assertEquals($hdNode->getChainCode()->getContents(), $success->getNode()->getChainCode()->getContents());
        $this->assertEquals($hdNode->getChildNum(), $success->getNode()->getChildNum());
    }

    public function testRequirePin()
    {
        $pinRequest = new PinMatrixRequest();
        $pinRequest->setType(PinMatrixRequestType::PinMatrixRequestType_Current());

        $path = [44|0x80000000, 0|0x80000000, 0|0x80000000, 0, 0];
        $hdNode = new HDNodeType();
        $hdNode->setDepth(5);
        $hdNode->setChildNum(0);
        $hdNode->setFingerprint(3980090076);
        $hdNode->setChainCode(\Protobuf\Stream::fromString(hex2bin("36770a996cb1995a8ec87be4a45d2513a1ac43cbe96bfb950ce4a121f3354cee")));
        $hdNode->setPublicKey(\Protobuf\Stream::fromString(hex2bin("0284538fa544ca7070f3548959819669312ee462aed6eb04ba443a97183376cbfe")));

        $publicKey = new PublicKey();
        $publicKey->setNode($hdNode);
        $publicKey->setXpub("xprvA4Apf1Dg1po8BsqxwFb22gfDpJzoTrRDTTbspdoZjZE3BkYfWoWphDaxQXcAji7ciLagegZ2q8kU2yjfE9gs6EEDsbhZMMT1t3ivCGupCqk");

        $codec = new HexCodec();

        $httpStack = new MockHttpStack(
            "http://localhost:21325",
            [],
            new Response(200, [], $codec->encode(MessageType::MessageType_PinMatrixRequest()->value(), $pinRequest)),
            new Response(200, [], $codec->encode(MessageType::MessageType_PublicKey()->value(), $publicKey))
        );

        $httpClient = $httpStack->getClient();
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $reqFactory = new RequestFactory();
        $getPublicKey = $reqFactory->getPublicKey('Bitcoin', $path, false);

        $pinInput = $this->getMockSinglePinInput('12345');

        $getPublicKeyService = new GetPublicKeyService();
        $success = $getPublicKeyService->call($session, $pinInput, $getPublicKey);

        $this->assertInstanceOf(PublicKey::class, $success);
        $this->assertEquals($publicKey->getXpub(), $success->getXpub());
        $this->assertEquals($hdNode->getDepth(), $success->getNode()->getDepth());
        $this->assertEquals($hdNode->getChainCode()->getContents(), $success->getNode()->getChainCode()->getContents());
        $this->assertEquals($hdNode->getChildNum(), $success->getNode()->getChildNum());
    }

    public function testReturnsUnexpectedType()
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
        $path = [44|0x80000000, 0|0x80000000, 0|0x80000000, 0, 0];
        $getPublicKey = $reqFactory->getPublicKey('Bitcoin', $path, false);

        $pinInput = new CurrentPinInput(new CommandLineUserInputRequest());
        $getPublicKeyService = new GetPublicKeyService();

        $this->expectException(UnexpectedResultException::class);
        $this->expectExceptionMessage("Unexpected response, expecting PublicKey, got BitWasp\\TrezorProto\\Features");

        $getPublicKeyService->call($session, $pinInput, $getPublicKey);
    }
}
