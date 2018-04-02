<?php

declare(strict_types=1);

use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Http\HttpClient;

require __DIR__ . "/../vendor/autoload.php";

$useNetwork = "BTC";

$httpClient = HttpClient::forUri("http://localhost:21325");
$trezor = new Client($httpClient);

echo "list devices\n";
$devices = $trezor->listDevices();
if (empty($devices)) {
    throw new \Exception("Error! No devices connected!");
}

foreach ($devices->devices() as $device) {
    var_dump($device);
    echo "path: {$device->path}\n";
    echo "session: {$device->session}\n";
    echo "product: {$device->product}\n";
    echo "vendor: {$device->vendor}\n";
    echo "\n";
}
