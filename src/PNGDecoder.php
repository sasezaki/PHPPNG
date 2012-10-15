<?php
include_once dirname(__FILE__) . '/ByteArray.php';
include_once dirname(__FILE__) . '/PNGDecodeException.php';
include_once dirname(__FILE__) . '/PNGDataDecoder.php';

class PNGDecoder
{
    protected function readHeader(&$h)
    {
        if ( "\x89PNG\r\n\x1a\n" !== fread($h, 8)) throw new PNGDecodeException;
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

        // PLTEチャンク以外無視
        while ((list($name,) = end($chunks)) && $name !== 'IDAT') {
            if ($name === 'PLTE') list(, $plte) = array_pop($chunks);
            else                  array_pop($chunks);
        }
        $info['color'] !== 3 or isset($plte)
            or raise(new PNGDecodeException('PLTEチャンクが適切な位置にありません'));

        // IDATチャンクの連続を連結して得る
        $bin = '';
        while ((list($name,) = end($chunks)) && $name === 'IDAT') {
            list(, $body) = array_pop($chunks);
            $bin .= $body;
        }
        
        $data = @gzuncompress($bin)
            or raise(new PNGDecodeExcpetion('データ本体の解凍に失敗しました'));
        $bytearr = new ByteArray($data);
                
        $image = ref(new PNGDataDecoder)->decode($bytearr, $info);
                
        return $image;
    }
    
    protected function parseIHDR(Array $chunk)
    {
        list($name, $body) = $chunk;
        $name === 'IHDR' or raise(new PNGDecodeException('IHDRヘッダが無効です'));
        $ret = unpack('Nwidth/Nheight/Cbit/Ccolor/Ccompress/Cfilter/Cinterlace', $body);
        
        in_array($ret['bit'], array(1, 2, 4, 8, 16))
            or raise(new PNGDecodeException('ビット深度が無効です'));

        in_array($ret['color'], array(2, 4, 6)) and in_array($ret['bit'], array(8, 16))
            or $ret['color'] === 0 and in_array($ret['bit'], array(1, 2, 4, 8, 16))
            or $ret['color'] === 3 and in_array($ret['bit'], array(1, 2, 4, 8))
            or raise(new PNGDecodeException('カラータイプとビット深度の組み合わせが無効です'));

        $ret['compress'] === 0
            or raise(new PNGDecodeExcpetion('未知の圧縮方式です'));

        $ret['filter'] === 0
            or raise(new PNGDecodeExcpetion('未知のフィルタリング方式です'));

        $ret['interlace'] === 0
            or raise(new PNGDecodeExcpetion('インターレースには対応していません'));
                     
        return $ret;
    }

    protected function readChunk(&$h)
    {
        $len = array_val(unpack('N', fread($h, 4)), 1);
        
        $name = fread($h, 4);
        $body = $len > 0 ? fread($h, $len) : '';
        $crc = array_val(unpack('N', fread($h, 4)), 1);

        $crc === crc32($name . $body)
            or raise(new PNGDecodeExcpetion('crc32が適切ではありません'));
        
        return array($name, $body);
    }
}






