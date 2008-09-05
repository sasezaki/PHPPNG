<?php

class BinaryReader
{
    protected $arr = array(), $i = 0;
    
    function __construct($body)
    {
        foreach (str_split($body) as $char) {
            $this->arr = array_merge($this->arr, $this->parseChar($char));
        }
        
    }
    protected function parseChar($c)
    {
        list(, $c) = unpack('C', $c);
        $ret = array();
        for ($i = 0; $i < 8; $i++) {
            $ret[] = ($c & 1) ? true : false;
            $c >>= 1;
        }
        return array_reverse($ret);
    }
    function getIndex()
    {
        return $this->i;
    }
    function setIndex($i)
    {
        assert(is_int($i));
        
        $this->i = $i;
    }
    
    function readBit()
    {
        $this->i++;
        return $this->arr[$this->i - 1] ? 1 : 0;
    }
    
    function readByte()
    {
        return $this->read(8);
    }

    function read($n)
    {
        assert($n > 0 && is_int($n));
        $ret = array_slice($this->arr, $this->i, $n);
        $this->i += $n;
        return $this->asInt($ret);
    }

    function readBitArray($n)
    {
        assert($n > 0 && is_int($n));
        $ret = array_slice($this->arr, $this->i, $n);
        $this->i += $n;
        return $ret;
    }
    
    
    function back($n)
    {
        assert($n > 0 && is_int($n) && $this->i >= $n);
        $this->i -= $n;
        assert($i >= 0);
        
    }
    
    protected function asInt(Array $ba)
    {
        $ret = 0;
        for ($i = 0, $len = count($ba); $i < $len; $i++) {
            $ret |= $ba[$i] ? 1 : 0;
            if ($i + 1 < $len) $ret <<= 1;
        }
        return $ret;
    }

    function getBitArray()
    {
        return $this->arr;
    }
    
}

if (is_main()) 
{
    $ba = new BinaryReader("\xff\xaa");

    d(dechex($ba->readByte()));
    d(dechex($ba->readByte()));
}
