<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Exception;

use BitWasp\Trezor\Device\Exception\Failure as FailureErr;
use BitWasp\TrezorProto\Failure;
use BitWasp\TrezorProto\FailureType;

abstract class FailureException extends DeviceException
{
    const MAP_ERROR = [
        FailureType::Failure_UnexpectedMessage_VALUE => FailureErr\UnexpectedMessageException::class,
        FailureType::Failure_ButtonExpected_VALUE => FailureErr\ButtonExpectedException::class,
        FailureType::Failure_DataError_VALUE => FailureErr\DataErrorException::class,
        FailureType::Failure_ActionCancelled_VALUE => FailureErr\ActionCancelledException::class,
        FailureType::Failure_PinExpected_VALUE => FailureErr\PinExpectedException::class,
        FailureType::Failure_PinCancelled_VALUE => FailureErr\PinCancelledException::class,
        FailureType::Failure_PinInvalid_VALUE => FailureErr\PinInvalidException::class,
        FailureType::Failure_InvalidSignature_VALUE => FailureErr\InvalidSignatureException::class,
        FailureType::Failure_ProcessError_VALUE => FailureErr\ProcessErrorException::class,
        FailureType::Failure_NotEnoughFunds_VALUE => FailureErr\NotEnoughFundsException::class,
        FailureType::Failure_NotInitialized_VALUE => FailureErr\NotInitializedException::class,
        FailureType::Failure_PinMismatch_VALUE => FailureErr\PinMismatchException::class,
        FailureType::Failure_FirmwareError_VALUE => FailureErr\FirmwareErrorException::class,
    ];

    /**
     * @param Failure $failure
     * @throws self
     */
    public static function handleFailure(Failure $failure)
    {
        $concrete = FailureErr\UnknownError::class;
        if ($code = $failure->getCode()) {
            if (array_key_exists($code->value(), self::MAP_ERROR)) {
                $concrete = self::MAP_ERROR[$code->value()];
            }
        }

        throw new $concrete($failure->getMessage(), 0);
    }
}
