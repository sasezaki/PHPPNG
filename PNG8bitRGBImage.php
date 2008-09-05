<?php
include_once dirname(__FILE__) . '/IImage.php';

class PNG8bitRGBImage implements IImage
{
    protected $ba, $width, $height;
    function __construct(ByteArray $ba, $width, $height)
    {
        $t = $this;
        list($t->ba, $t->width, $t->height) = func_get_args();
    }
    function getWidth()
    {
        return $this->width;
    }
    function getHeight()
    {
        return $this->height;
    }
    function getPixel($x, $y)
    {
        $i = ($this->width * 3 + 1) * $y + $x * 3 + 1;
        return array($this->ba[$i], $this->ba[$i + 1], $this->ba[$i + 2]);
    }
    function setPixel($x, $y, Array $p)
    {
        throw new Exception;
    }
}
