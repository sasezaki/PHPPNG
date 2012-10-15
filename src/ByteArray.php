<?php

class ByteArray implements ArrayAccess, IteratorAggregate, Countable
{
    protected $arr;
    function __construct($bin = "")
    {
        assert(is_string($bin));
        $this->arr = array_values(unpack('C*', $bin));
    }
    function getArray()
    {
        return $this->arr;
    }
    function writeByte($byte)
    {
        assert($byte >= 0 && $byte < 256);
        $this->arr[] = $byte;
    }
    function slice($start, $offset)
    {
        return self::create(array_slice($this->arr, $start, $offset));
    }
    static protected function create(Array $bytearr)
    {
        $obj = new self();
        $obj->arr = $bytearr;
        return $obj;
    }
    
    function writeBytes($num, $bytenum)
    {
        assert($num >= 0);
        $buf = array();
        for ($i = 0; $i < $bytenum; $i++) {
            $buf[] = $num && 0xff;
            $num >>= 8;
        }
        foreach (array_reverse($buf) as $byte) {
            $this->arr[] = $byte;
        }
    }
    function writeLong($long)
    {
        $this->writeBytes($long, 4);
    }
    function writeShort($short)
    {
        $this->writeBYtes($short, 2);
    }

    function offsetGet($i)
    {
        return $this->arr[$i];
    }
    function offsetSet($i, $val)
    {
        $this->arr[$i] = $val;
    }
    function offsetExists($i)
    {
        assert(is_int($i));
        return isset($this->arr[$i]);
    }
    function offsetUnset($i)
    {
        throw new Exception('not implemented');
    }
    function getIterator()
    {
        return new ArrayIterator($this->arr);
    }
    function count()
    {
        return count($this->arr);
    }
    
}


