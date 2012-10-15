<?php

include dirname(__DIR__).'/vendor/autoload.php';

use PHPPNG\PNGEncoder;
use PHPPNG\PNGDecoder;
use PHPPNG\PNG8bitRGBImage;
use PHPPNG\ByteArray;

$decoder = new PNGDecoder;
$image = $decoder->decode($argv[1]);

class Filter extends PNG8bitRGBImage
    //implements IImage
{
    private $r, $g, $b;
    private $count = 0;

    public function getPixel($x, $y)
    {
        if ($this->count > rand(1,180)) {
            $this->count = 0;
            $this->r = 0;
            $this->g = 0;
            $this->b = 0;
        } else {
            $this->count++;
        }
        list($r, $g, $b) = $pixel = parent::getPixel($x , $y);

        
        return array(
                     $this->r = ($r > $this->r) ? $r : $this->r,
                     $this->g = ($g > $this->g) ? $g : $this->g,
                     $this->b = ($b > $this->b) ? $b : $this->b,
                     );
    }

    public static function wrap(PNG8bitRGBImage $image)
    {
        $reflection = new ReflectionObject($image);
        $p = $reflection->getProperty('ba');
        $p->setAccessible(true);
        $ba = $p->getValue($image);

        $ba = static::defaultFilter($ba);
    
        return new static($ba, $image->getWidth(), $image->getHeight());
    }

    public static function defaultFilter($ba)
    {
        return  ByteArrayExt::wrap($ba);
    }
}

class ByteArrayExt extends ByteArray
{
    static public function wrap(ByteArray $ba)
    {
        $obj = new static;
        $obj->arr = $ba->getArray();
        return $obj;
    }

    public function offsetGet($i)
    {
        $byte = parent::offsetGet($i); 
        if (preg_match('/\w/', chr($byte))) {
            return rand(0, 256);
        }
        return $byte;
    }
}

$image = Filter::wrap($image);

$encoder = new PNGEncoder;
$encoder->write(__DIR__. '/out.png', $image);
