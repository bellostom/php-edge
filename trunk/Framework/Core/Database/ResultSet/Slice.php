<?php
namespace Framework\Core\Database\ResultSet;

use Framework\Core\Database\DB;

class Slice implements \ArrayAccess, \Iterator, \Countable {
    private $instance;
    public $start;
    public $length;
    public $counter = 0;

    public function __construct($instance, $start, $length) {
        $this->length = $length;
        $this->start = $start;
        $this->instance = $instance;
    }

    public function toArray() {
        return iterator_to_array($this, true);
    }

    public function offsetGet($offset) {
        return $this->instance->offsetGet($offset);
    }

    public function offsetExists($offset) {
        return ($offset < $this->instance->totalRows &&
            $this->counter < $this->length);
    }

    public function offsetSet($offset, $value) {
        throw new \Exception("This collection is read only.");
    }

    public function offsetUnset($offset) {
        throw new \Exception("This collection is read only.");
    }

    public function count()	{
        $df = $this->instance->totalRows - $this->start;
        if($df >= $this->length) {
            return $this->length;
        }
        return $df;
    }

    public function current() {
        return $this->instance->current();
    }

    public function key() {
        return $this->instance->key();
    }

    public function next() {
        $this->counter++;
        return $this->instance->next();
    }

    public function rewind() {
        $this->counter = 0;
        $this->instance->currentIndex = $this->start;
    }

    public function valid() {
        return $this->offsetExists($this->instance->currentIndex);
    }

    public function append($value) {
        throw new \Exception("This collection is read only");
    }
}
?>