<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\UserInput;

class CurrentPassphraseInput implements CurrentPassphraseInputInterface
{
    /**
     * @var UserInputRequest
     */
    private $inputRequest;

    public function __construct(UserInputRequest $inputRequest)
    {
        $this->inputRequest = $inputRequest;
    }

    public function getPassphrase(): string
    {
        echo "It's your safe and trusted pin entry!\n";
        echo "Enter your passphrase to proceed: ";
        return trim($this->inputRequest->getInput());
    }
}
