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
    protected $sql_mode = ' ';

    /**
     * MysqlSlave constructor.
     *
     * @param array $settings To set MySQL session sql_mode to MySQL ver. 5.7 default use a setting like:
     *
     * @example     'sql_mode' => 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,
     *                             ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION',
     *
     *                        Do not pass 'sql_mode' setting to fallback to old handling of ' '.
     *
     */
    public function __construct(array $settings){
        $this->host = $settings['host'];
        $this->db = $settings['db'];
        $this->user = $settings['user'];
        $this->pass = $settings['pass'];
        if(array_key_exists('timezone', $settings)){
            $this->timezone = $settings['timezone'];
        }
        if (isset($settings['sql_mode'])) {
            $this->sql_mode = $settings['sql_mode'];
        }
    }

    protected function connect() {
        list($host, $port) = explode(':', $this->host);
        $this->link = new \mysqli($host, $this->user,	$this->pass, $this->db, $port);
        if($this->link->connect_errno){
            throw new Core\Exceptions\EdgeException("Error connecting to the {$this->db} db. Error: ". $this->link->connect_error);
        }
        $this->link->set_charset('utf8');
        if($this->timezone !== false){
            $this->link->query(sprintf("SET time_zone='%s'", $this->timezone));
        }
        // Set @@SESSION.sql_mode for this session to overcome a possible different server @@GLOBAL.sql_mode.
        if (isset($this->sql_mode)) {
            $this->link->query(sprintf("SET SESSION sql_mode='%s'", $this->sql_mode));
        }
    }

    public function dbMetadata($table) {
        static $cache = [];
        if(!isset($cache[$table])){
            $result = $this->dbQuery("SELECT * FROM $table limit 1");
            $finfo = $result->fetch_fields();
            $store = array();
            foreach($finfo as $column) {
                $store[$column->name] = array($column->type, $column->flags);
            }
            $cache[$table] = $store;
        }
        return $cache[$table];
    }

    public function now() {
        $q = "SELECT NOW() as n";
        return $this->dbFetchOne($q);
    }

    public function dbFetchArray($rs) {
        return $rs->fetch_array(MYSQLI_ASSOC);
    }

    public function dbFetchAll($rs){
        if(!method_exists($rs, 'fetch_all')){
            $data = array();
            while($r = $this->dbFetchArray($rs)){
                $data[] = $r;
            }
            return $data;
        }
        return $rs->fetch_all(MYSQLI_ASSOC);
    }

    public function dbFetchObject($rs, $classname=null) {
        if(!is_null($classname)) {
            return $rs->fetch_object($classname);
        }
        return $rs->fetch_object();
    }

    public function dbQuery($q) {
        if(!$this->isAlive()) {
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
            }else if ($err_no == 1451 || $err_no == 1452) {
                throw new Core\Exceptions\ForeignKeyException($message);
            }
            else{
                throw new Core\Exceptions\QueryException($message);
            }
        }
        return $res;
    }

    public function dbSeek($rs, $index) {
        $rs->data_seek($index);
    }

    public function dbInsertId() {
        return $this->link->insert_id;
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
        return $this->link->real_escape_string($str);
    }

    public function dbNumRows($rs) {
        return $rs->num_rows;
    }

    public function dbErrno() {
        return $this->link->errno;
    }

    public function dbError() {
        return $this->link->error;
    }

    public function __destruct() {
        if(!is_null($this->link)) {
            $this->link->close();
        }
    }

    protected function isAlive() {
        return !is_null($this->link) && $this->link->ping();
    }
}