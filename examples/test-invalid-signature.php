<?php

declare(strict_types=1);

use BitWasp\Trezor\Device\Command\InitializeService;
use BitWasp\Trezor\Device\Command\VerifyMessageService;
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
$reqFactory = new RequestFactory();

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
$verifyMsgService = new VerifyMessageService();

$address = "1HksNAfGmaMYAAzidJcAdgfjXy89ajYWpD";
$signature = base64_decode("HywU/GSkCe1fghjTt/D9YPA2pTXSZUfcT3WNn5XpnZGIcfQvuEZH2LGAXiBTsypIITmrwXF8LxZWq5MCLo/kxp0=");
$message = "this is a message!";

$verifyMessage = $reqFactory->verifyMessage($btcNetwork->getCoinName(), $address, $signature, $message);

$verifiedMessage = $verifyMsgService->call($session, $verifyMessage);
var_dump($verifiedMessage);

$session->release();
