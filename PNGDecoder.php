<?php
include_once dirname(__FILE__) . '/ByteArray.php';
include_once dirname(__FILE__) . '/PNGDecodeException.php';
include_once dirname(__FILE__) . '/PNGDataDecoder.php';

class PNGDecoder
{
    protected function readHeader(&$h)
    {
        if ( "\x89PNG\r\n\x1a\n" !== fread($h, 8)) throw new Exception;
    }

    protected function readChunks($path)
    {
        $f = fopen($path, 'rb');
        $this->readHeader($f);
        $chunks = array();
        while (filesize($path) > ftell($f))
            $chunks[] = $this->readChunk($f);
        fclose($f);
        return $chunks;      
    }

    function decode($path)
    {
        if (!file_exists($path)) throw new Exception;
        $chunks = array_reverse($this->readChunks($path));
        $info = $this->parseIHDR(array_pop($chunks));

        // PLTE$B%A%c%s%/0J30L5;k(B
        while ((list($name,) = end($chunks)) && $name !== 'IDAT') {
            if ($name === 'PLTE') list(, $plte) = array_pop($chunks);
            else                  array_pop($chunks);
        }
        $info['color'] !== 3 or isset($plte)
            or raise(new PNGDecodeException('PLTE$B%A%c%s%/$,E,@Z$J0LCV$K$"$j$^$;$s(B'));

        // IDAT$B%A%c%s%/$NO"B3$rO"7k$7$FF@$k(B
        $bin = '';
        while ((list($name,) = end($chunks)) && $name === 'IDAT') {
            list(, $body) = array_pop($chunks);
            $bin .= $body;
        }
        
        $data = @gzuncompress($bin)
            or raise(new PNGDecodeExcpetion('$B%G!<%?K\BN$N2rE`$K<:GT$7$^$7$?(B'));
        $bytearr = new ByteArray($data);
                
        $image = ref(new PNGDataDecoder)->decode($bytearr, $info);
                
        return $image;
    }
    
    protected function parseIHDR(Array $chunk)
    {
        list($name, $body) = $chunk;
        $name === 'IHDR' or raise(new PNGDecodeException('IHDR$B%X%C%@$,L58z$G$9(B'));
        $ret = unpack('Nwidth/Nheight/Cbit/Ccolor/Ccompress/Cfilter/Cinterlace', $body);
        
        in_array($ret['bit'], array(1, 2, 4, 8, 16))
            or raise(new PNGDecodeException('$B%S%C%H?<EY$,L58z$G$9(B'));

        in_array($ret['color'], array(2, 4, 6)) and in_array($ret['bit'], array(8, 16))
            or $ret['color'] === 0 and in_array($ret['bit'], array(1, 2, 4, 8, 16))
            or $ret['color'] === 3 and in_array($ret['bit'], array(1, 2, 4, 8))
            or raise(new PNGDecodeException('$B%+%i!<%?%$%W$H%S%C%H?<EY$NAH$_9g$o$;$,L58z$G$9(B'));

        $ret['compress'] === 0
            or raise(new PNGDecodeExcpetion('$BL$CN$N05=LJ}<0$G$9(B'));

        $ret['filter'] === 0
            or raise(new PNGDecodeExcpetion('$BL$CN$N%U%#%k%?%j%s%0J}<0$G$9(B'));

        $ret['interlace'] === 0
            or raise(new PNGDecodeExcpetion('$B%$%s%?!<%l!<%9$K$OBP1~$7$F$$$^$;$s(B'));
                     
        return $ret;
    }

    protected function readChunk(&$h)
    {
        $len = array_val(unpack('N', fread($h, 4)), 1);
        
        $name = fread($h, 4);
        $body = $len > 0 ? fread($h, $len) : '';
        $crc = array_val(unpack('N', fread($h, 4)), 1);

        $crc === crc32($name . $body)
            or raise(new PNGDecodeExcpetion('crc32$B$,E,@Z$G$O$"$j$^$;$s(B'));
        
        return array($name, $body);
    }
}






