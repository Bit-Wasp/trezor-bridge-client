<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Device\Debug;

use BitWasp\Trezor\Device\DebugMessage;
use BitWasp\TrezorProto\DebugLinkGetState;
use BitWasp\TrezorProto\DebugLinkState;

class DebugGetStateTest extends DebugCommandTest
{
    public function testGetState()
    {
        $getState = new DebugLinkGetState();
        $state = $this->session->sendMessage(DebugMessage::getState($getState));
        $this->assertInstanceOf(DebugLinkState::class, $state);
        /** @var DebugLinkState $state */
        $this->assertNull($state->getPin());
        $this->assertFalse($state->getPassphraseProtection());
        $this->assertFalse($state->getPassphraseProtection());
    }
}
