<?php

declare(strict_types=1);

use BitWasp\Trezor\Device\Command\InitializeService;
use BitWasp\Trezor\Device\Command\SignMessageService;
use BitWasp\Trezor\Device\PinInput\CurrentPinInput;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\TrezorProto\CoinType;

require __DIR__ . "/../vendor/autoload.php";

$useNetwork = "BTC";
$trezor = \BitWasp\Trezor\Bridge\Client::fromUri("http://localhost:21325");

$hardened = pow(2, 31)-1;
echo "list devices\n";
$devices = $trezor->listDevices();
if (empty($devices)) {
    throw new \Exception("Error! No devices connected!");
}

echo "first device\n";
$firstDevice = $devices->devices()[0];

print_r($firstDevice);

echo "acquire device!\n";
$session = $trezor->acquire($firstDevice);
$sessionId = $session->getSessionId();
$reqFactory = new RequestFactory();

echo "sessionId: {$sessionId}\n";
echo "devicePath: {$session->getDevice()->getPath()}\n";

$initializeCmd = new InitializeService();
$features = $initializeCmd->call($session, $reqFactory->initialize());

$btcNetwork = null;
foreach ($features->getCoinsList() as $coin) {
    /** @var CoinType $coin */
    if ($coin->getCoinShortcut() === $useNetwork) {
        $btcNetwork = $coin;
    }
}

if (!$btcNetwork) {
    throw new \RuntimeException("Failed to find requested network ({$useNetwork})");
}

$toSign = "this is my message!";

$currentPinInput = new CurrentPinInput();
$signMessageService = new SignMessageService();

$bip44Account1 = [44 | $hardened, 0 | $hardened, 0 | $hardened];
$address0 = array_merge($bip44Account1, [0, 0]);

$signMessage = $reqFactory->signMessagePubKeyHash($btcNetwork->getCoinName(), [1], $toSign);

$signedMessage = $signMessageService->call($session, $currentPinInput, $signMessage);

echo "address: {$signedMessage->getAddress()}\n";
echo "    msg: `$toSign`\n";
echo "    sig: ".base64_encode($signedMessage->getSignature()->getContents())."\n";
$session->release();
