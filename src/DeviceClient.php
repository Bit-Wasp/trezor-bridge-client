<?php

declare(strict_types=1);

namespace BitWasp\Trezor;

use BitWasp\TrezorProto;
use GuzzleHttp\Psr7\Stream;
use Protobuf\Message;
use Psr\Http\Message\StreamInterface;

class DeviceClient
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
     * @param string $string
     * @return resource
     */
    private function stringToStream(string $string)
    {
        $stream = fopen('php://memory', 'w+');
        if (!is_resource($stream)) {
            throw new \RuntimeException("Failed to create stream");
        }

        $wrote = fwrite($stream, $string);
        if ($wrote !== strlen($string)) {
            throw new \RuntimeException("Failed to write to stream");
        }

        rewind($stream);
        return $stream;
    }

    public function convertHexPayloadToBinary(StreamInterface $hexStream): StreamInterface
    {
        if ($hexStream->getSize() < 12) {
            throw new \Exception("Malformed data (size too small)");
        }

        return new Stream($this->stringToStream(pack("H*", $hexStream->getContents())));
    }

    public function parsePayload(StreamInterface $stream): array
    {
        $type = unpack('n', $stream->read(2))[1];
        $stream->seek(2);

        $size = unpack('N', $stream->read(4))[1];
        $stream->seek(6);

        if ($size > ($stream->getSize() - 6)) {
            throw new \Exception("Malformed data (sent more than size)");
        }

        return [$type, $this->stringToStream($stream->read($size))];
    }

    /**
     * @param int $messageType
     * @param Message $message
     * @return array
     * @throws \Exception
     */
    public function sendDeviceMessage(int $messageType, Message $message)
    {
        $result = $this->session->sendMessage($messageType, $message);

        return $this->parsePayload(
            $this->convertHexPayloadToBinary($result->getBody())
        );
    }

    public function getFeatures(): TrezorProto\Features
    {
        $getFeatures = new TrezorProto\GetFeatures();

        list ($type, $result) = $this->sendDeviceMessage(
            TrezorProto\MessageType::MessageType_GetFeatures_VALUE,
            $getFeatures
        );

        if (TrezorProto\MessageType::MessageType_GetFeatures_VALUE !== $type) {
            throw new \Exception("Invalid message returned");
        }

        return TrezorProto\Features::fromStream($result);
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
     * @return TrezorProto\Features|\Protobuf\Message
     * @throws \Exception
     */
    public function initialize()
    {
        list ($type, $result) = $this->sendDeviceMessage(
            TrezorProto\MessageType::MessageType_Initialize_VALUE,
            new TrezorProto\Initialize()
        );

        return TrezorProto\Features::fromStream($result);
    }
}
