<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge\Message;

abstract class DeviceListResponse implements \Countable
{
    /**
     * @var Device[]
     */
    private $devices = [];

    /**
     * @param Device[] $devices
     */
    public function __construct(Device... $devices)
    {
        $this->devices = $devices;
    }

    public function count(): int
    {
        return count($this->devices);
    }

    /**
     * @return Device[]
     */
    public function devices(): array
    {
        return $this->devices;
    }
}
