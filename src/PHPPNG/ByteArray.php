<?php
namespace PHPPNG;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;
use Exception;

class ByteArray implements ArrayAccess, IteratorAggregate, Countable
{
    protected $arr;
    public function __construct($bin = "")
    {
        assert(is_string($bin));
        $this->arr = array_values(unpack('C*', $bin));
    }
    public function getArray()
    {
        return $this->arr;
    }
    public function writeByte($byte)
    {
        assert($byte >= 0 && $byte < 256);
        $this->arr[] = $byte;
    }
    public function slice($start, $offset)
    {
        return self::create(array_slice($this->arr, $start, $offset));
    }
    protected static function create(Array $bytearr)
    {
        $obj = new self();
        $obj->arr = $bytearr;

        return $obj;
    }

    public function writeBytes($num, $bytenum)
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
    public function writeLong($long)
    {
        $this->writeBytes($long, 4);
    }
    public function writeShort($short)
    {
        $this->writeBYtes($short, 2);
    }

    public function offsetGet($i)
    {
        return $this->arr[$i];
    }
    public function offsetSet($i, $val)
    {
        $this->arr[$i] = $val;
    }
    public function offsetExists($i)
    {
        assert(is_int($i));

        return isset($this->arr[$i]);
    }
    public function offsetUnset($i)
    {
        throw new Exception('not implemented');
    }
    public function getIterator()
    {
        return new ArrayIterator($this->arr);
    }
    public function count()
    {
        return count($this->arr);
    }

}
