<?php
namespace Framework\Core\Database;

use Framework\Core;

class MysqlSlave {

    protected $link = null;
    protected $host;
    protected $db;
    protected $user;
    protected $pass;
    public $isTransactional = false;

    public function __construct($host, $db, $user, $pass){
        $this->host = $host;
        $this->db = $db;
        $this->user = $user;
        $this->pass = $pass;
    }

    protected function connect() {
        list($host, $port) = explode(':', $this->host);
        $this->link = new \mysqli($host, $this->user,	$this->pass, $this->db, $port);
        if($this->link->connect_errno){
            throw new Core\Exceptions\AppException("Error connecting to the {$db} db. Error: ". $this->link->connect_error);
        }
        $this->link->set_charset('utf8');
    }

    public function db_metadata($table) {
        $result = $this->db_query("SELECT * FROM $table limit 1");
        $finfo = $result->fetch_fields();
        $store = array();
        foreach($finfo as $column) {
            $store[$column->name] = array($column->type, $column->flags);
        }
        return $store;
    }

    public function now() {
        $q = "SELECT NOW() as n";
        return $this->db_fetch_one($q);
    }

    public function db_fetch_array($rs) {
        return $rs->fetch_array(MYSQLI_ASSOC);
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
                throw new Core\Exceptions\AppException($message);
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

    public function __destruct() {
        if(!is_null($this->link)) {
            $this->link->close();
        }
        $this->isTransactional = false;
    }

    protected function is_alive() {
        return !is_null($this->link) && $this->link->ping();
    }
}
?>