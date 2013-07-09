<?php
namespace Edge\Core\Database;

use Edge\Core;

class MysqlSlave {

    protected $link = null;
    protected $host;
    protected $db;
    protected $user;
    protected $pass;
    protected $timezone = false;
    CONST CONN_STRING = 'mysql:host=%s;port=%s;dbname=%s;charset=utf8';

    public function __construct(array $settings){
        $this->host = $settings['host'];
        $this->db = $settings['db'];
        $this->user = $settings['user'];
        $this->pass = $settings['pass'];
        if(array_key_exists('timezone', $settings)){
            $this->timezone = $settings['timezone'];
        }
    }

    protected function connect() {
        list($host, $port) = explode(':', $this->host);
        $dsn = sprintf(MysqlSlave::CONN_STRING, $host, $port, $this->db);
        $this->link = new \PDO($dsn, $this->user, $this->pass);
        $this->link->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
       /* $this->link = new \mysqli($host, $this->user,	$this->pass, $this->db, $port);
        if($this->link->connect_errno){
            throw new Core\Exceptions\EdgeException("Error connecting to the {$this->db} db. Error: ". $this->link->connect_error);
        }
        $this->link->set_charset('utf8');*/
        if($this->timezone !== false){
            $this->link->exec(sprintf("SET time_zone='%s'", $this->timezone));
        }
    }

    public function dbMetadata($table) {
        if(!$this->isAlive()) {
            $this->connect();
        }
        $q = $this->link->query("DESC $table");
        $data = $q->fetchAll(\PDO::FETCH_ASSOC);
        $q->closeCursor();
        $store = [];
        foreach($data as $info){
            $store[$info['Field']] = $info['Extra'];
        }
        return $store;
    }

    public function now() {
        $q = "SELECT NOW() as n";
        return $this->dbFetchOne($q);
    }

    public function dbFetchArray(\PDOStatement $rs) {
        return $rs->fetch(\PDO::FETCH_ASSOC);
    }

    public function dbFetchAll(\PDOStatement $rs){
        return $rs->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function dbFetchObject($rs, $classname=null) {
        if(!is_null($classname)) {
            return $rs->fetch_object($classname);
        }
        return $rs->fetch_object();
    }

    public function dbQuery($q, array $params=[]) {
        if(!$this->isAlive()) {
            $this->connect();
        }
        $stmt = $this->link->prepare($q);
        try{
            $stmt->execute($params);
            return $stmt;
        }catch(\PDOException $e){
            $errNo = $e->errorInfo[1];
            $message = sprintf("Error executing query %s. Error was %s. Error Code %s ",
                $q, $e->getMessage(), $errNo);
            if($errNo == 1213) {
                throw new Core\Exceptions\DeadLockException($message);
            }else if ($errNo == 1062) {
                throw new Core\Exceptions\DuplicateEntry($message);
            }else{
                throw new Core\Exceptions\EdgeException($message);
            }
        }
    }

    public function dbSeek($rs, $index) {
        $rs->data_seek($index);
    }

    public function dbInsertId() {
        return $this->link->lastInsertId();
    }

    public function dbFoundRows() {
        return $this->dbFetchOne("SELECT FOUND_ROWS() as t");
    }

    public function dbFetchOne($q) {
        $result = $this->dbQuery($q);
        $result->data_seek(0);
        $row = $result->fetch_array(MYSQLI_NUM);
        $result->close();
        return $row[0];
    }

    public function dbFetchOneAssoc($q) {
        $result = $this->dbQuery($q);
        $result->data_seek(0);
        $result->close();
        return $result->fetch_assoc();
    }

    public function dbEscapeString($str) {
        if(!$this->isAlive()) {
            $this->connect();
        }
        return $this->link->quote($str);
    }

    public function dbNumRows($rs) {
        return $rs->num_rows;
    }

    public function __destruct() {
        $this->link = null;
    }

    protected function isAlive() {
        return !is_null($this->link);
    }
}