<?php
namespace PHPPNG;

interface IImage
{
    public function getPixel($x, $y);
    public function setPixel($x, $y, Array $p);
    public function getHeight();
    public function getWidth();
}
