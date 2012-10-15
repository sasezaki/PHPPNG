<?php
include_once dirname(__FILE__) . '/IImage.php';

class PNGEncoder
{
    protected $h = null;

    function write($absolute_path, IImage $image)
    {
        assert(is_string($absolute_path));
                
        $this->h = @fopen($absolute_path, 'wb');
        if (!$this->h) return false;
        $this->writeHeader();
        $this->writeIHDRChunk($image->getWidth(), $image->getHeight(), 8, 2);
        $this->writeImage($image);
        $this->writeIENDChunk();
        fclose($this->h);
        $this->h = null;    
        return true;
    }
    protected function writeImage(IImage $image)
    {
        $buf = array();
        foreach (range(0, $image->getHeight() - 1) as $y) {
            $line = '';
            $line .= pack('C', 0);
            foreach (range(0, $image->getWidth() - 1) as $x) {
                $p = $image->getPixel($x, $y);
                $line .= pack('CCC', $p[0], $p[1], $p[2]);
            }
            $buf[] = $line;
        }
        $this->writeIDATChunks(implode('', $buf));
    }
    
    
    protected function writeHeader()
    {
        fwrite($this->h, "\x89PNG\r\n\x1a\n");
    }

    protected function writeIHDRChunk($width, $height, $bit, $color)
    {
        $this->writeChunk('IHDR',
            pack('NNCCCCC', $width, $height, $bit, $color, 0, 0, 0));
    }
    
    protected function writeIDATChunks($body)
    {
        $this->writeChunk('IDAT', gzcompress($body));
    }
    
    
    protected function writeIENDChunk()
    {
        $this->writeChunk('IEND', '');
    }
    
    
    function writeChunk($name, $body)
    {
        $len = strlen($body);
        $crc = pack('N', crc32($name . $body));
        fwrite($this->h, pack('N', $len) . $name . $body . $crc);
    }
}



