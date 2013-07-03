<?php
namespace Edge\Core\Database;

use Edge\Core\Edge;

/**
 * Class MongoConnection
 * Responsible for interacting with the mongo
 * database. Basic CRUD methods implemented
 * @package Edge\Core\Database
 */
class MongoConnection extends \MongoClient {

    private $db;

    public function __construct(array $settings){
        $host = isset($settings['host'])?$settings['host']:'localhost';
        $options = array(
            "connect" => false
        );
        if(isset($settings['auth'])){
            $options['username'] = $settings['user'];
            $options['password'] = $settings['pass'];
        }
        parent::__construct("mongodb://" + $host, $options);
        $this->db = $settings['db'];
    }

    /**
     * Return a Collection object
     * @param $collection
     * @return \MongoCollection
     */
    protected function getCollection($collection){
        return $this->{$this->db}->$collection;
    }

    /**
     * Execute a select query on the selected collection
     * @param $collection
     * @param array $options
     * @return \MongoCursor
     */
    public function find($collection, array $options, $fields=array(),
                         $order=array(), $limit=null, $offset=0){
        $rs = $this->getCollection($collection)->find($options, $fields);
        if($order){
            $rs->sort($order);
        }
        if($limit !== null){
            $rs->limit($limit);
        }
        if($offset){
            $rs->skip($offset);
        }
        return $rs;
    }

    /**
     * Insert a document into the selected collection
     * @param $collection
     * @param array $data
     * @param array $options
     * @return array|bool
     */
    public function insert($collection, array $data, array $options=array()){
        return $this->getCollection($collection)->insert($data, $options);
    }

    /**
     * Delete a document from the selected collection
     * @param $collection
     * @param \MongoId $id
     * @param array $options
     * @return mixed
     */
    public function delete($collection, \MongoId $id, $options=array()){
        return $this->getCollection($collection)->remove(array("_id" => $id), $options);
    }

    /**
     * Update a record
     * @param $collection
     * @param array $where update params
     * @param array $data
     * @param array $options
     * @return bool
     */
    public function update($collection, array $where, array $data, $options=array()){
        return $this->getCollection($collection)->update($where, $data, $options);
    }
}