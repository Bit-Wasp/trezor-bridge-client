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
        return trim($this->inputRequest->getInput("It's your safe and trusted pin entry!\nEnter your passphrase to proceed: "));
    }
}
