<?php
//namespace Edge\Core\Database;

//use Edge\Core;

class MongoConnection extends \MongoClient {

    protected $collection;
    protected $db;

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
        $this->db = $this->selectDB($settings['db']);
    }

    public function setCollection($name){
        $this->collection = new \MongoCollection($this->db, $name);
        return $this->collection;
    }

    public function find($collection, array $options=array(), $sort=array()) {
        $cursor = $this->setCollection($collection)->find($options);
        if($sort){
            $cursor->sort($sort);
        }
        return $cursor;
    }

    public function db_fetch_all(){
        return $rs->fetch_all(MYSQLI_ASSOC);
    }

    public function db_fetch_object($rs, $classname=null) {
        if(!is_null($classname)) {
            return $rs->fetch_object($classname);
        }
        return $rs->fetch_object();
    }

    public function db_query($q) {
        if(!$this->is_alive()) {
            $this->connect();
        }
        $res = $this->link->query($q);
        if(!$res) {
            $err_no = $this->link->errno;
            $message = sprintf("Error executing query %s. Error was %s. Error Code %s ",
                $q, $this->link->error, $err_no);
            if($err_no == 1213) {
                throw new Core\Exceptions\DeadLockException($message);
            }else if ($err_no == 1062) {
                throw new Core\Exceptions\DuplicateEntry($message);
            }else{
                throw new Core\Exceptions\EdgeException($message);
            }
        }
        return $res;
    }

    public function db_seek($rs, $index) {
        $rs->data_seek($index);
    }

    public function db_insert_id() {
        return $this->link->insert_id;
    }

    public function db_found_rows() {
        return $this->db_fetch_one("SELECT FOUND_ROWS() as t");
    }

    public function db_fetch_one($q) {
        $result = $this->db_query($q);
        $result->data_seek(0);
        $row = $result->fetch_array(MYSQLI_NUM);
        $result->close();
        return $row[0];
    }

    public function db_fetch_one_assoc($q) {
        $result = $this->db_query($q);
        $result->data_seek(0);
        $result->close();
        return $result->fetch_assoc();
    }

    public function db_escape_string($str) {
        if(!$this->is_alive()) {
            $this->connect();
        }
        return $this->link->real_escape_string($str);
    }

    public function db_num_rows($rs) {
        return $rs->num_rows;
    }

    public function db_errno() {
        return $this->link->errno;
    }

    public function db_error() {
        return $this->link->error;
    }

    protected function is_alive() {
        return !is_null($this->link) && $this->link->ping();
    }
}
?>