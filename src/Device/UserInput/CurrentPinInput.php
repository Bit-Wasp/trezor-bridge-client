<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\UserInput;

class CurrentPinInput implements CurrentPinInputInterface
{
    /**
     * @var UserInputRequest
     */
    private $inputRequest;

    public function __construct(UserInputRequest $inputRequest)
    {
        $this->inputRequest = $inputRequest;
    }

    public function getPin(): string
    {
        echo "It's your safe and trusted pin entry!\n";
        echo "Enter your pin to proceed: ";
        return trim($this->inputRequest->getInput());
    }
}
