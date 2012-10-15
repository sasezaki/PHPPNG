<?php
namespace PHPPNG;

use Exception;

class BilinerResizedImage implements IImage
{
    protected $image, $width, $height, $ratio_w, $ratio_h;
    public function __construct(IIMage $image, $width, $height)
    {
        assert(self::is_uint($width, $height));
        list($this->image, $this->width, $this->height) = func_get_args();
        $this->ratio_w = ($image->getWidth() - 1) / ($width - 1);
        $this->ratio_h = ($image->getHeight() - 1) / ($height - 1);
    }
    public function getWidth()
    {
        return $this->width;
    }
    public function getHeight()
    {
        return $this->height;
    }

    public function getPixel($x, $y)
    {
        $img = $this->image;
        $mx = $x * $this->ratio_w; $my = $y * $this->ratio_h;
        $basex = (int) floor($mx); $basey = (int) floor($my);

        $arr = array(array($basex, $basey),
                     array($basex + 1, $basey),
                     array($basex, $basey + 1),
                     array($basex + 1, $basey + 1));

        foreach ($arr as &$elt) {
            $elt = $elt[0] < $img->getWidth() && $elt[1] < $img->getHeight() ?
                $img->getPixel($elt[0], $elt[1]) : false;
        }

        $bwx = $mx - $basex; $bwy = $my - $basey;
        $weights = array((1 - $bwx) * (1 - $bwy),
                         $bwx * (1 - $bwy),
                         (1 - $bwx) * $bwy,
                         $bwx * $bwy);

        foreach ($arr as $i => &$elt) if ($elt) {
            foreach ($elt as &$sample) {
                $sample *= $weights[$i];
            }
        }

        $_arr = $arr;
        $arr = array();
        foreach ($_arr as &$elt) if ($elt) {
            $arr[] = $elt;
        }

        while (count($arr) > 1) {
            $p1 = array_pop($arr);
            $p2 = array_pop($arr);
            array_push($arr, array($p1[0] + $p2[0], $p1[1] + $p2[1], $p1[2] + $p2[2]));
        }

        foreach ($arr[0] as &$elt) {
            $elt = round($elt);
        }

        return $arr[0];
    }
    public function setPixel($x, $y, Array $p)
    {
        throw new Exception('not implemented');
    }

    public static function is_uint()
    {
        foreach (func_get_args() as $arg) {
            if (!is_int($arg)) return false;
            if ($arg < 0) return false;
        }

        return true;
    }
}
