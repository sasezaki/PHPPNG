<?php

interface IImage
{
    function getPixel($x, $y);
    function setPixel($x, $y, Array $p);
    function getHeight();
    function getWidth();
}
