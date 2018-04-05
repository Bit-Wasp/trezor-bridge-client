<?php

declare(strict_types=1);

use BitWasp\Trezor\Device\Command\InitializeService;
use BitWasp\Trezor\Device\Command\VerifyMessageService;
use BitWasp\Trezor\Device\UserInput\CurrentPinInput;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\Trezor\Device\UserInput\CommandLineUserInputRequest;
use BitWasp\Trezor\Device\Util;

require __DIR__ . "/../vendor/autoload.php";

$useNetwork = "BTC";
$trezor = \BitWasp\Trezor\Bridge\Client::fromUri("http://localhost:21325");

$devices = $trezor->listDevices();
if (empty($devices)) {
    throw new \Exception("Error! No devices connected!");
}

$firstDevice = $devices->devices()[0];

echo "acquire device!\n";
$session = $trezor->acquire($firstDevice);
$reqFactory = new RequestFactory();

$initializeCmd = new InitializeService();
$features = $initializeCmd->call($session, $reqFactory->initialize());

if (!($btcNetwork = Util::networkByCoinShortcut($useNetwork, $features))) {
    throw new \RuntimeException("Failed to find requested network ({$useNetwork})");
}

$currentPinInput = new CurrentPinInput(new CommandLineUserInputRequest());
$verifyMsgService = new VerifyMessageService();

$address = "1HksNAfGmaMYAAzidJcAdgfjXy89ajYWpD";
$signature = base64_decode("HywU/GSkCe1fghjTt/D9YPA2pTXSZUfcT3WNn5XpnZGIcfQvuEZH2LGAXiBTsypIITmrwXF8LxZWq5MCLo/kxp0=");
$message = "this is my message!";

$verifyMessage = $reqFactory->verifyMessage($btcNetwork->getCoinName(), $address, $signature, $message);

$verifiedMessage = $verifyMsgService->call($session, $verifyMessage);

$session->release();
