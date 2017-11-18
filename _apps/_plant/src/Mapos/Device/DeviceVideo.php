<?php

namespace Mapos\Device;

class DeviceVideo
{
    use DeviceTrait;

    //My camera needs to heat up
    private $heatupCameraSec = 3;
    private $storagePath = './storage/';

    private $width = 1600;
    private $height = 1200;

    private $thumbWidth = 300;
    private $thumbHeight = 225;

    public function __construct()
    {
        $this->setDevicesPath("/dev/video");
        @mkdir($this->storagePath . 'thumb/', 0777, true);
    }

    public function captureImage($onlyThumb = false)
    {
        $this->findDevice();

        if (!$this->device) {
            $this->warning('device video not set! Camera is connected?');
            return false;
        }

        $file = $this->generateFilename();
        sleep(1);
        $cmdThumb = "convert -thumbnail 300 " . $this->getFilePath($file) . " " . $this->getThumbnailPath();

        $debugCommand = "";

        if (!DEBUG) {
            $debugCommand = "-loglevel quiet";
        }
        //For only thumb we want to use camera little as possible.
        $cmd = "avconv " . $debugCommand . " -f video4linux2 -s " . ($onlyThumb ? $this->thumbWidth : $this->width) . "x" . ($onlyThumb ? $this->thumbHeight : $this->height) . " -i " . $this->device . " -ss 0:0:" . $this->heatupCameraSec . " -frames 1 " . $this->getFilePath($file) . " && " . $cmdThumb;
        //echo $cmd;
        exec($cmd, $output, $return);

        if($onlyThumb){
            $file = $this->getFilePath($file);

            if(file_exists($file)){
                unlink($file);
            }

        }

        //chmod($thumbPath, 0777);
        return !$output && !$return;
    }

    private function generateFilename()
    {
        return date("Ymd-His") . ".jpg";
    }

    private function getThumbnailPath($file = null)
    {
        if(!$file){
            $file = "preview.jpg";
        }
        return $this->storagePath . "thumb/" . $file;
    }

    private function getFilePath($file)
    {
        return $this->storagePath . $file;
    }

    public function captureThumbnail()
    {
        $this->captureImage(true);
    }

    /**
     * @return mixed
     */
    public function getStoragePath()
    {
        return $this->storagePath;
    }

    /**
     * @param mixed $storagePath
     */
    public function setStoragePath($storagePath)
    {
        $this->storagePath = $storagePath;
    }
}