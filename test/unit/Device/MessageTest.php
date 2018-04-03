<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device;

use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\ButtonAck;
use BitWasp\TrezorProto\ClearSession;
use BitWasp\TrezorProto\GetAddress;
use BitWasp\TrezorProto\GetEntropy;
use BitWasp\TrezorProto\GetPublicKey;
use BitWasp\TrezorProto\Initialize;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PassphraseAck;
use BitWasp\TrezorProto\Ping;
use BitWasp\TrezorProto\PinMatrixAck;
use BitWasp\TrezorProto\SignMessage;
use BitWasp\TrezorProto\VerifyMessage;

class MessageTest extends TestCase
{
    public function testGetPublicKey()
    {
        $m = new GetPublicKey();
        $msg = Message::getPublicKey($m);
        $this->assertSame($m, $msg->getProto());
        $this->assertEquals(MessageType::MessageType_GetPublicKey()->value(), $msg->getType());
    }

    public function testGetAddress()
    {
        $m = new GetAddress();
        $msg = Message::getAddress($m);
        $this->assertSame($m, $msg->getProto());
        $this->assertEquals(MessageType::MessageType_GetAddress()->value(), $msg->getType());
    }

    public function testGetEntropy()
    {
        $m = new GetEntropy();
        $msg = Message::getEntropy($m);
        $this->assertSame($m, $msg->getProto());
        $this->assertEquals(MessageType::MessageType_GetEntropy()->value(), $msg->getType());
    }

    public function testPinMatrixAck()
    {
        $m = new PinMatrixAck();
        $msg = Message::pinMatrixAck($m);
        $this->assertSame($m, $msg->getProto());
        $this->assertEquals(MessageType::MessageType_PinMatrixAck()->value(), $msg->getType());
    }

    public function testPassphraseAck()
    {
        $m = new PassphraseAck();
        $msg = Message::passphraseAck($m);
        $this->assertSame($m, $msg->getProto());
        $this->assertEquals(MessageType::MessageType_PassphraseAck()->value(), $msg->getType());
    }

    public function testInitialize()
    {
        $m = new Initialize();
        $msg = Message::initialize($m);
        $this->assertSame($m, $msg->getProto());
        $this->assertEquals(MessageType::MessageType_Initialize()->value(), $msg->getType());
    }

    public function testButtonAck()
    {
        $m = new ButtonAck();
        $msg = Message::buttonAck($m);
        $this->assertSame($m, $msg->getProto());
        $this->assertEquals(MessageType::MessageType_ButtonAck()->value(), $msg->getType());
    }

    public function testSignMessage()
    {
        $m = new SignMessage();
        $msg = Message::signMessage($m);
        $this->assertSame($m, $msg->getProto());
        $this->assertEquals(MessageType::MessageType_SignMessage()->value(), $msg->getType());
    }

    public function testPing()
    {
        $m = new Ping();
        $msg = Message::ping($m);
        $this->assertSame($m, $msg->getProto());
        $this->assertEquals(MessageType::MessageType_Ping()->value(), $msg->getType());
    }

    public function testClearSession()
    {
        $m = new ClearSession();
        $msg = Message::clearSession($m);
        $this->assertSame($m, $msg->getProto());
        $this->assertEquals(MessageType::MessageType_ClearSession()->value(), $msg->getType());
    }

    public function testVerifyMessage()
    {
        $m = new VerifyMessage();
        $msg = Message::verifyMessage($m);
        $this->assertSame($m, $msg->getProto());
        $this->assertEquals(MessageType::MessageType_VerifyMessage()->value(), $msg->getType());
    }
}
