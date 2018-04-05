<?php

declare(strict_types=1);

use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Device\Command\InitializeService;
use BitWasp\Trezor\Device\Command\PingService;
use BitWasp\Trezor\Device\UserInput\CurrentPassphraseInput;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\Trezor\Device\UserInput\CurrentPinInput;
use BitWasp\Trezor\Device\UserInput\CommandLineUserInputRequest;
use BitWasp\Trezor\Device\Util;

require __DIR__ . "/../vendor/autoload.php";

$useNetwork = "BTC";
$httpClient = HttpClient::forUri("http://localhost:21325");
$trezor = new Client($httpClient);

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

if (!($btcNetwork = Util::networkByCoinShortcut($useNetwork, $features))) {
    throw new \RuntimeException("Failed to find requested network ({$useNetwork})");
}

$pingService = new PingService();

$userInput = new CommandLineUserInputRequest();
$pinInput = new CurrentPinInput($userInput);
$passInput = new CurrentPassphraseInput($userInput);

$nonce = random_bytes(16);

// the false flags here determine what the user should be challenged with
$ping = $reqFactory->ping($nonce, false, false, false);

$success = $pingService->call($session, $ping, $pinInput, $passInput);
var_dump($success);
$session->release();
