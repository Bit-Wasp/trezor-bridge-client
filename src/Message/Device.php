<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Message;

class Device
{
    /**
     * @var \stdClass - hid path for device
     */
    private $msg;

    /**
     * Device constructor.
     * @param string $path
     * @param string|null $session
     * @param int|null $vendor
     * @param int|null $product
     */
    public function __construct(\stdClass $device)
    {
        $this->msg = $device;
    }

    public function __get($name)
    {
        return $this->msg->$$name;
    }

    public function getPath(): string
    {
        return $this->msg->path;
    }

    public function getSession()
    {
        return $this->msg->session;
    }

    public function getVendor()
    {
        return $this->msg->vendor;
    }

    public function getProduct()
    {
        return $this->msg->product;
    }

    public function getObject(): \stdClass
    {
        return $this->msg;
    }
}
