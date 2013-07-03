<?php
namespace Edge\Models\Adapters;

use Edge\Core\Edge,
    Edge\Core\Exceptions\EdgeException,
    Edge\Models\Record;

class MongoAdapter extends BaseAdapter{

    /** Override base method to define
     * the $in keyword required by Mongo
     * in order to execute the query
     * @param array $args
     * @return $this
     */
    public function in(array $args){
        $lastVar = $this->lastVar;
        $key = $this->lastWhere;
        $this->{$lastVar}[$key] = array('$in' => $args);
        return $this;
    }

    /**
     * Build the array the find options
     * @return array
     * @throws \Edge\Core\Exceptions\EdgeException
     */
    protected function getQuery(){
        $options = array();
        $fields = $this->selectFields;
        if($fields[0] == '*'){
            $this->selectFields = array();
        }
        else{
            $this->selectFields = array_combine($fields, array_fill(0, count($fields), 1));
        }
        if(count($this->where) > 0){
            $options = $this->where;
            if(count($this->and) > 0){
                $options = array_merge($options, $this->and);
            }
            if($this->or){
                if($this->and){
                    throw new EdgeException("Cannot mix or and and clauses with Mongo");
                }
                $options = array('$or' => array($options, $this->or));
            }
        }
        return $options;
    }

    /**
     * Execute the find query
     * @param $options
     * @return mixed
     */
    public function executeQuery($options){
        $db = $this->getDbConnection();
        $fields = $this->selectFields;
        return $db->find($this->table, $options, $fields, $this->order,
                         $this->limit, $this->offset);
    }

    /**
     * Persist data to the collection
     * @param Record $entry
     */
    public function save(Record $entry){
        $data = $entry->getAttributes();
        unset($data['_id']);
        $this->getDbConnection()->insert($entry::getTable(), $data);
        $entry->_id = $data['_id'];
    }

    /**
     * Delete record
     * @param Record $entry
     */
    public function delete(Record $entry){
        $this->getDbConnection()->delete($entry::getTable(), $entry->_id);
    }

    /**
     * Update record
     * @param Record $entry
     */
    public function update(Record $entry){
        $where = array("_id" => $entry->_id);
        $data = $entry->getAttributes();
        $this->getDbConnection()->update($entry::getTable(), $where, $data);
    }

    /**
     * Return the Mongo db connection
     * @return mixed
     */
    public function getDbConnection(){
        return Edge::app()->mongo;
    }

    /**
     * Return a Mongo ResultSet
     * @param $rs
     * @param $class
     * @return \Edge\Core\Database\ResultSet\MongoResultSet
     */
    protected function getResultSet($rs, $class){
        return new \Edge\Core\Database\ResultSet\MongoResultSet($rs, $class);
    }

    /**
     * Iterate Mongo cursor and return an array
     * with the records
     * @param $rs
     * @return array
     */
    protected function fetchAll($rs){
        $data = array();
        foreach($rs as $row){
            $data[] =  $row;
        }
        return $data;
    }

    /**
     * Return an assoc array for the first record
     * @param $rs
     * @return mixed
     */
    protected function fetchArray($rs){
        foreach($rs as $row){
            return $row;
        }
    }

    /**
     * We implement many to many relationships without the
     * need of a lookup table. This means that the below
     * method should never be invoked for Mongo models
     * @param $model
     * @param array $attrs
     * @throws \Edge\Core\Exceptions\EdgeException
     */
    public function manyToMany($model, array $attrs){
        throw new EdgeException("Mongo implements this relationship differently.");
    }

    /**
     * Count the Mongo cursor object
     * @param $rs
     * @return mixed
     */
    protected function countResults($rs){
        return $rs->count();
    }
}