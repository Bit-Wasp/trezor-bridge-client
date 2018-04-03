<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge;

use BitWasp\Trezor\Bridge\Codec\CallMessage\HexCodec;
use BitWasp\Trezor\Bridge\Exception\InactiveSessionException;
use BitWasp\Trezor\Device\Exception\Failure\ActionCancelledException;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\Failure;
use BitWasp\TrezorProto\FailureType;
use BitWasp\TrezorProto\Features;
use BitWasp\TrezorProto\GetPublicKey;
use BitWasp\TrezorProto\HDNodeType;
use BitWasp\TrezorProto\Initialize;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PublicKey;
use GuzzleHttp\Psr7\Response;

use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Bridge\Session;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class SessionTest extends TestCase
{
    public function testIsActive()
    {
        $httpClient = HttpClient::forUri("http://localhost:21215");
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $sessionId = '1';
        $session = new Session($client, $device, $sessionId);
        $this->assertEquals($sessionId, $session->getSessionId());
        $this->assertTrue($session->isActive());
        $this->assertSame($device, $session->getDevice());
    }

    public function testCalledOnReleasedSession()
    {
        $requests = [
            new Response(200, ['Content-Type' => 'application/json'], \json_encode((object) [])),
        ];

        // Create a mock and queue two responses.
        $mock = new MockHandler($requests);

        /** @var RequestInterface[] $container */
        $container = [];
        $history = Middleware::history($container);

        // Add the history middleware to the handler stack.
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $httpClient = HttpClient::forUri("http://localhost:21325/", ['handler' => $stack,]);
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');
        $this->assertTrue($session->isActive());
        $session->release();

        $this->assertFalse($session->isActive());
    }

    public function testThrowsWhenUsedAndInactive()
    {
        $requests = [
            new Response(200, ['Content-Type' => 'application/json'], \json_encode((object) [])),
        ];

        // Create a mock and queue two responses.
        $mock = new MockHandler($requests);

        /** @var RequestInterface[] $container */
        $container = [];
        $history = Middleware::history($container);

        // Add the history middleware to the handler stack.
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $httpClient = HttpClient::forUri("http://localhost:21325/", ['handler' => $stack,]);
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');
        $session->release();

        $msg = new Message(MessageType::MessageType_Initialize(), new Initialize());

        $this->expectException(InactiveSessionException::class);

        $session->sendMessage($msg);
    }

    public function testMessageSend()
    {
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
        $retMsg = $codec->encode(MessageType::MessageType_PublicKey()->value(), $publicKey);
        $requests = [
            new Response(200, [], '0011000002450a11626974636f696e7472657a6f722e636f6d100118062000321836423635333939463643414335463943424430383045343738014000520e74657374696e672d7472657a6f725a240a07426974636f696e120342544318002080897a2805489ee4a22450e4dba224580168005a270a07546573746e6574120454455354186f2080ade20428c40148cf8fd621509487d621580168005a240a0542636173681203424348180020a0c21e2805489ee4a22450e4dba2245800600068015a260a084e616d65636f696e12034e4d4318342080ade204280548e2c8f60c50feb9f60c580068005a260a084c697465636f696e12034c544318302080b48913283248e2c8f60c50feb9f60c580168005a280a08446f6765636f696e1204444f4745181e208094ebdc03281648fd95eb17509887eb17580068005a220a0444617368120444415348184c20a08d06281048cca5f91750f8a5f917580068005a240a055a6361736812035a454318b83920c0843d28bd39489ee4a22450e4dba224580068005a2b0a0c426974636f696e20476f6c641203425447182620a0c21e2817489ee4a22450e4dba2245801604f68015a250a0844696769427974651203444742181e20a0c21e2805489ee4a22450e4dba224580168005a270a084d6f6e61636f696e12044d4f4e41183220c096b1022837489ee4a22450e4dba2245801680060016a14723cf295a72ce07b96047901bb8c2e461a2488f872207651b7caba5aae0cc1c65c8304f760396f77606cd3990c991598f0e22a81e0077800800100880100980100a00100'),
            new Response(200, [], $retMsg),
        ];

        // Create a mock and queue two responses.
        $mock = new MockHandler($requests);

        /** @var RequestInterface[] $container */
        $container = [];
        $history = Middleware::history($container);

        // Add the history middleware to the handler stack.
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $httpClient = HttpClient::forUri("http://localhost:21325/", ['handler' => $stack,]);
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $features = $session->sendMessage(new Message(MessageType::MessageType_Initialize(), new Initialize()));
        $this->assertInstanceOf(Features::class, $features);

        $addressNList = new \Protobuf\ScalarCollection([44 | 0x80000000, 0 | 0x80000000, 0 | 0x80000000, 0, 0]);
        $getPublicKey = new GetPublicKey();
        $getPublicKey->setCoinName("Bitcoin");
        $getPublicKey->setAddressNList($addressNList);
        $getPublicKey->setShowDisplay(false);

        $publicKey = $session->sendMessage(new Message(MessageType::MessageType_GetPublicKey(), $getPublicKey));
        $this->assertInstanceOf(PublicKey::class, $publicKey);
    }

    public function testMessageSendWithFailureActionCancelled()
    {
        $failure = new Failure();
        $failure->setCode(FailureType::Failure_ActionCancelled());
        $failure->setMessage("Action cancelled by user");

        $codec = new HexCodec();
        $retMsg = $codec->encode(MessageType::MessageType_Failure()->value(), $failure);
        $requests = [
            new Response(200, [], '0011000002450a11626974636f696e7472657a6f722e636f6d100118062000321836423635333939463643414335463943424430383045343738014000520e74657374696e672d7472657a6f725a240a07426974636f696e120342544318002080897a2805489ee4a22450e4dba224580168005a270a07546573746e6574120454455354186f2080ade20428c40148cf8fd621509487d621580168005a240a0542636173681203424348180020a0c21e2805489ee4a22450e4dba2245800600068015a260a084e616d65636f696e12034e4d4318342080ade204280548e2c8f60c50feb9f60c580068005a260a084c697465636f696e12034c544318302080b48913283248e2c8f60c50feb9f60c580168005a280a08446f6765636f696e1204444f4745181e208094ebdc03281648fd95eb17509887eb17580068005a220a0444617368120444415348184c20a08d06281048cca5f91750f8a5f917580068005a240a055a6361736812035a454318b83920c0843d28bd39489ee4a22450e4dba224580068005a2b0a0c426974636f696e20476f6c641203425447182620a0c21e2817489ee4a22450e4dba2245801604f68015a250a0844696769427974651203444742181e20a0c21e2805489ee4a22450e4dba224580168005a270a084d6f6e61636f696e12044d4f4e41183220c096b1022837489ee4a22450e4dba2245801680060016a14723cf295a72ce07b96047901bb8c2e461a2488f872207651b7caba5aae0cc1c65c8304f760396f77606cd3990c991598f0e22a81e0077800800100880100980100a00100'),
            new Response(200, [], $retMsg),
        ];

        // Create a mock and queue two responses.
        $mock = new MockHandler($requests);

        /** @var RequestInterface[] $container */
        $container = [];
        $history = Middleware::history($container);

        // Add the history middleware to the handler stack.
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $httpClient = HttpClient::forUri("http://localhost:21325/", ['handler' => $stack,]);
        $client = new Client($httpClient);
        $device = new Device($this->createDevice('hidabcd1234', 21325, 1));
        $session = new Session($client, $device, '1');

        $features = $session->sendMessage(new Message(MessageType::MessageType_Initialize(), new Initialize()));
        $this->assertInstanceOf(Features::class, $features);

        $addressNList = new \Protobuf\ScalarCollection([44 | 0x80000000, 0 | 0x80000000, 0 | 0x80000000, 0, 0]);
        $getPublicKey = new GetPublicKey();
        $getPublicKey->setCoinName("Bitcoin");
        $getPublicKey->setAddressNList($addressNList);
        $getPublicKey->setShowDisplay(true);

        $this->expectException(ActionCancelledException::class);
        $this->expectExceptionMessage("Action cancelled by user");

        $session->sendMessage(new Message(MessageType::MessageType_GetPublicKey(), $getPublicKey));
    }
}
