<?php

namespace Mapos\Device;

use PhpSerial;

class DeviceSerial
{
    use DeviceTrait;
    private $serialObject;
    /**
     * @var integer
     */
    private $baudRate = 9600;

    public function __construct()
    {
        $this->setDevicesPath("/dev/ttyACM");
        $this->serialObject = new PhpSerial();
    }

    private function setDevice()
    {
        if (!$this->device) {
            if (!$this->findDevice()) {
                $this->warning("Device not found.");
                return false;
            } else {
                $this->serialObject->deviceSet($this->device);
                $this->serialObject->confBaudRate($this->baudRate);
                //$this->serialObject->_ckOpened() ||
                sleep(5);
                if ($this->serialObject->deviceOpen()) {
                    $this->info('Device found: ' . $this->device);
                } else {
                    $this->warning('Can\'t connect device: ' . $this->device);
                }
            }
        } else {
            if (!file_exists($this->device)) {
                $this->warning("Device disconnected?");
                $this->serialObject->deviceClose();
                $this->device = false;
                $this->setDevice();
            }
        }
    }

    /**
     * @param $data
     */
    public function send($data, $waitForReply = 0.1)
    {
        $this->setDevice();
        if ($this->device) {
            $this->serialObject->sendMessage($data, $waitForReply);
        } else {
            $this->warning("Device not found for send.");
        }
    }

    /**
     * @return mixed|string
     */
    public function read()
    {
        $this->setDevice();
        if ($this->device) {
            $read = $this->serialObject->readPort();
            $read = @str_replace("\n", "", explode("\n", $read)[0]);
            return $read;
        } else {
            $this->warning("Device not found for read.");
            sleep(5);
        }
    }
}