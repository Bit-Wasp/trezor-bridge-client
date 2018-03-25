<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Exception;

class SchemaValidationException extends TrezorException
{
    private $errors = [];

    public function __construct(
        array $errors,
        string $message = "",
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
