<?php
namespace Edge\Core\Database\ResultSet;

/**
 * Abstract ResultSet class to represent multiple records
 * returned by a persistence layer
 * As the resultset is iterated, a new instance (denoted by $className)
 * is returned
 */
abstract class ResultSet implements \Iterator, \Countable, \ArrayAccess {

    protected $currentIndex;
    protected $totalRows = 0;
    protected $result;
    protected $className;

    public function __construct($rs, $object){
        $this->currentIndex = 0;
        $this->result = $rs;
        $this->className = $object;
        $this->setRows();
    }

    /**
     * Set the number of rows within the ResultSet
     */
    abstract protected function setRows();

    /**
     * Return a new instance for the current record
     */
    abstract protected function getRecord($offset);

    /**
     * Transform the iterator to an array
     */
    public function toArray(){
        return iterator_to_array($this);
    }

    /**
     * Countable interface implementation
     * Returns the number of rows
     */
    public function count(){
        return $this->totalRows;
    }

    /**
     * Iterator implementation
     * @return mixed
     */
    public function current(){
        return $this->offsetGet($this->currentIndex);
    }

    /**
     * Iterator implementation
     * @return int
     */
    public function key(){
        return $this->currentIndex;
    }

    /**
     * Iterator implementation
     * @return int
     */
    public function next(){
        return $this->currentIndex++;
    }

    /**
     * Iterator implementation
     */
    public function rewind(){
        $this->currentIndex = 0;
    }

    /**
     * Iterator
     */
    public function valid(){
        return $this->offsetExists($this->currentIndex);
    }

    /**
     * ArrayAccess interface
     */
    public function offsetGet($offset){
        return $this->getRecord($offset);
    }

    /**
     * ArrayAccess interface
     */
    public function offsetExists($offset){
        return ($offset < $this->totalRows);
    }

    /**
     * ArrayAccess interface
     */
    public function offsetSet($offset,$value){
        throw new \Exception("This collection is read only.");
    }

    /**
     * ArrayAccess interface
     */
    public function offsetUnset($offset){
        throw new \Exception("This collection is read only.");
    }
}