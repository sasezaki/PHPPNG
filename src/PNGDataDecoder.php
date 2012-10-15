<?php
include_once dirname(__FILE__) . '/PNGDecodeException.php';
include_once dirname(__FILE__) . '/ByteArray.php';
include_once dirname(__FILE__) . '/PNG8bitRGBImage.php';

class PNGDataDecoder
{
    function decode(ByteArray $ba, Array $info, Array $plte = null)
    {
        assert(isset($info['height'], $info['width'], $info['bit'],
                     $info['color'], $info['filter'], $info['interlace'])
               && $info['height'] > 0 && $info['width'] > 0
               && $info['color'] & (1 << 2) ? !is_null($plte) : true);

        // color type2, 8bitの場合        
        if ($info['color'] === 2 && $info['bit'] === 8) {
            return $this->decodeWith8bitRGB($ba, $info);
        }
        else throw new PNGDecodeException('このビット深度とカラータイプに組み合わせには対応していません');
    }
    protected function decodeWith8bitRGB(ByteArray $ba, Array $info)
    {           
        $this->unfilter($ba, $info['width'], $info['height'], 3);
        return new PNG8bitRGBImage($ba, $info['width'], $info['height']);
    }
    protected function unfilter(ByteArray $ba, $width, $height, $bpp)
    {
        $i = 0;
        for ($y = 0; $y < $height; $y++) {
            switch ($ba[$i++]) {
            case 0:
                $i += $width * $bpp;
                break;
            case 1:
                foreach (range(0, $width * $bpp -1) as $x) {
                    if ($x - $bpp >= 0) {
                        $ba->offsetSet($i,
                                       ($ba->offsetGet($i) +
                                        $ba->offsetGet($i - $bpp))
                                       & 0xff);
                    }
                    $i++;
                }
                break;
            case 2:
                for ($x = 0; $x < $width * $bpp; $x++) {
                    if($y > 0) {
                        $ba->offsetSet($i,
                                       ($ba->offsetGet($i) +
                                        $ba->offsetGet($i - $width * $bpp - 1))
                                       & 0xff);
                    }
                    $i++;
                }
                break;
            case 3:
                for ($x = 0; $x < $width * $bpp; $x++) {
                    $left = $x - $bpp >= 0
                        ? $ba->offsetGet($i - $bpp) : 0;
                    $above = $y - 1 >= 0
                        ? $ba->offsetGet($i - $width * $bpp - 1) : 0;
                    $ba->offsetSet($i,
                                   ($ba->offsetGet($i) +
                                    floor(($left + $above) / 2))
                                   & 0xff);
                    $i++;
                }
                break;
            case 4:
                for ($x = 0; $x < $width * $bpp; $x++) {
                    $left = $x - $bpp >= 0
                        ? $ba->offsetGet($i - $bpp) : 0;
                    $above = $y > 0
                        ? $ba->offsetGet($i - $width * $bpp - 1) : 0;
                    $upper_left = $x - $bpp >= 0 && $y > 0
                        ? $ba->offsetGet($i - $width * $bpp - 1 - $bpp) : 0;
                    $ba->offsetSet($i,
                                   ($ba->offsetGet($i) +
                                    $this->paethPredictor($left, $above, $upper_left))
                                   & 0xff);
                    $i++;
                }
                break;
            default: throw new PNGDecodeException('未知のフィルタです。');
                
            }
        }
    }
    
    protected function paethPredictor($a, $b, $c)
    {
        $p = $a + $b - $c;
        $pa = abs($p - $a);
        $pb = abs($p - $b);
        $pc = abs($p - $c);
        if ($pa <= $pb && $pa <= $pc) return $a;
        if ($pb <= $pc) return $b;
        return $c;
    }
}
