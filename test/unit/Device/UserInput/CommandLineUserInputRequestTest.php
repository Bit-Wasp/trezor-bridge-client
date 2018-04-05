<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\UserInput;

use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Device\UserInput\CommandLineUserInputRequest;

class CommandLineUserInputRequestTest extends TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testCommandLineInput()
    {
        $time = $this->getFunctionMock('BitWasp\Trezor\Device\UserInput', "readline");
        $time->expects($this->once())->willReturn("123456");

        $userInput = new CommandLineUserInputRequest();
        $input = $userInput->getInput("Enter your pin please: ");
        $this->assertEquals("123456", $input);
    }
}
