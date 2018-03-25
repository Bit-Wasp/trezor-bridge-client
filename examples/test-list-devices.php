<?php

declare(strict_types=1);

require __DIR__ . "/../vendor/autoload.php";

$useNetwork = "BTC";

$trezor = \BitWasp\Trezor\Bridge\Client::fromUri("http://localhost:21325");

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
