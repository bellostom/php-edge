<?php
namespace Edge\Core\Database\ResultSet;

use Edge\Core\Edge;

/**
 * Class MongoResultSet
 * Provides an iterator which loops a
 * Mongo cursor and returns a instance
 * of the specified class, for each iteration
 * @package Edge\Core\Database\ResultSet
 */
class MongoResultSet extends ResultSet{

	protected function setRows(){
        $this->totalRows = $this->result->count();
	}

	protected function getRecord($data){
		return new $this->className($data);
	}

    /**
     * Iterator implementation
     * @return mixed
     */
    public function current(){
        return $this->getRecord($this->result->current());
    }

    /**
     * Iterator implementation
     * @return int
     */
    public function key(){
        return $this->result->key();
    }

    /**
     * Iterator implementation
     * @return int
     */
    public function next(){
        return $this->result->next();
    }

    /**
     * Iterator implementation
     */
    public function rewind(){
        $this->result->rewind();
    }

    /**
     * Iterator
     */
    public function valid(){
        return $this->result->valid();
    }
}