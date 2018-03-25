<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\TrezorProto;

class Client
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param Message $message
     * @return Message
     * @throws \Exception
     */
    public function sendDeviceMessage(Message $message)
    {
        return $this->session->sendMessage($message);
    }

    public function getFeatures(): TrezorProto\Features
    {
        $getFeatures = new TrezorProto\GetFeatures();

        $response = $this->sendDeviceMessage(
            TrezorProto\MessageType::MessageType_GetFeatures_VALUE,
            $getFeatures
        );

        if (!$response->isType(TrezorProto\MessageType::MessageType_Features_VALUE)) {
            throw new \Exception("Invalid message returned");
        }

        return $response->getProto();
    }

    public function buttonAck()
    {
        $buttonAck = new TrezorProto\ButtonAck();

        return $this->sendDeviceMessage(
            TrezorProto\MessageType::MessageType_ButtonAck_VALUE,
            $buttonAck
        );
    }

    public function pinInput(int $pin): array
    {
        $pinMatrixAck = new TrezorProto\PinMatrixAck();
        $pinMatrixAck->setPin($pin);

        list ($type, $result) = $this->sendDeviceMessage(
            TrezorProto\MessageType::MessageType_PinMatrixAck_VALUE,
            $pinMatrixAck
        );

        var_dump($type);
        if ($type === TrezorProto\MessageType::MessageType_Failure_VALUE) {
            $failure = TrezorProto\Failure::fromStream($result);
            echo "code: {$failure->getCode()}\n";
            echo " msg: {$failure->getMessage()}\n";
            throw new \RuntimeException("failed entering pin!");
        }

        return [$type, $result];
    }

    protected function maybeAcknowledge($stream, $shouldAck, $expectRequestType)
    {
        if (!$shouldAck) {
            throw new \RuntimeException("Need to acknowledge entropy!");
        }

        $buttonRequest = TrezorProto\ButtonRequest::fromStream($stream);
        if ($buttonRequest->getCode() !== $expectRequestType) {
            throw new \RuntimeException("Unexpected button request (expected: {$expectRequestType}, got {$buttonRequest->getCode()}");
        }

        return $this->buttonAck();
    }

    public function getEntropy(int $bytes, bool $shouldAck = false)
    {
        $getEntropy = new TrezorProto\GetEntropy();
        $getEntropy->setSize($bytes);

        list ($type, $result) = $this->sendDeviceMessage(
            TrezorProto\MessageType::MessageType_GetEntropy_VALUE,
            $getEntropy
        );

        if ($type === TrezorProto\MessageType::MessageType_ButtonRequest_VALUE) {
            list ($type, $result) = $this->maybeAcknowledge($result, $shouldAck, TrezorProto\ButtonRequestType::ButtonRequest_ProtectCall_VALUE);
        }

        if ($type !== TrezorProto\MessageType::MessageType_Entropy_VALUE) {
            throw new \RuntimeException("Unexpected message returned, expecting getentropy");
        }

        return TrezorProto\Entropy::fromStream($result);
    }

    /**
     * @param string $coinName
     * @param array $path - list of uint32's, forming an absolute path to the public key
     * @param bool $shouldAck
     * @param string|null $curveName
     * @throws \Exception
     */
    public function getPublicKey(string $coinName, array $path, bool $shouldAck = false, string $curveName = null)
    {
        $getPublicKey = new TrezorProto\GetPublicKey();
        $getPublicKey->setCoinName($coinName);
        foreach ($path as $sequence) {
            $getPublicKey->addAddressN($sequence);
        }
        if ($curveName) {
            $getPublicKey->setEcdsaCurveName($curveName);
        }

        echo "sending request for pubkey\n";
        list ($type, $result) = $this->sendDeviceMessage(
            TrezorProto\MessageType::MessageType_GetPublicKey_VALUE,
            $getPublicKey
        );

        echo "might be pin request\n";
        var_dump($type, TrezorProto\MessageType::MessageType_PinMatrixRequest_VALUE);
        if ($type === TrezorProto\MessageType::MessageType_PinMatrixRequest_VALUE) {
            echo "got pin request\n";
            $pinMatrixRequest = TrezorProto\PinMatrixRequest::fromStream($result);
            if ($pinMatrixRequest->getType()->value() !== TrezorProto\PinMatrixRequestType::PinMatrixRequestType_Current_VALUE) {
                throw new \Exception("Unexpected pin matrix type (was {$pinMatrixRequest->getType()}, not expected value ".TrezorProto\PinMatrixRequestType::PinMatrixRequestType_Current_VALUE);
            }
            echo "enter your pin!\n";
            $pinin = (int) trim(fgets(STDIN));
            echo "ok, give to pin input {$pinin}\n";
            list ($type, $result) = $this->pinInput($pinin);
        }

        echo "might be public key\n";
        var_dump($type, TrezorProto\MessageType::MessageType_PublicKey_VALUE);
        if ($type !== TrezorProto\MessageType::MessageType_PublicKey_VALUE) {
            throw new \RuntimeException("Unexpected message returned ({$type}), expecting publickey" . TrezorProto\MessageType::MessageType_PublicKey_VALUE);
        }

        echo "was public key\n";
        return TrezorProto\PublicKey::fromStream($result);
    }

    /**
     * @return TrezorProto\Features
     * @throws \Exception
     */
    public function initialize(): TrezorProto\Features
    {
        $message = $this->sendDeviceMessage(
            new Message(TrezorProto\MessageType::MessageType_Initialize_VALUE, new TrezorProto\Initialize())
        );

        if (!$message->isType(TrezorProto\MessageType::MessageType_Features_VALUE)) {
            throw new \RuntimeException("Unexpected response");
        }

        return $message->getProto();
    }
}
